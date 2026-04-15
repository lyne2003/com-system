<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierSubcategoryService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplierSubcategoryController extends Controller
{
    public function index()
    {
        $rows = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('supplier_subcategories')) {
                $rows = DB::table('supplier_subcategories')->get()->toArray();
            }
        } catch (\Throwable $e) {
            $rows = [];
        }

        $matrix        = [];
        $subcategories = [];
        $suppliers     = [];

        foreach ($rows as $row) {
            $matrix[$row->supplier_name][$row->subcategory_name] = $row->count;
            $subcategories[$row->subcategory_name] = true;
            $suppliers[$row->supplier_name]        = true;
        }

        $subcategories = array_keys($subcategories);
        $suppliers     = array_keys($suppliers);
        usort($subcategories, fn($a, $b) => strcasecmp($a, $b));
        usort($suppliers,     fn($a, $b) => strcasecmp($a, $b));

        $isEmpty = empty($matrix);

        return view('supplier_subcategories.index', compact('matrix', 'subcategories', 'suppliers', 'isEmpty'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:20480',
        ]);

        $file      = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->uploadXlsx($file);
        }

        return $this->uploadCsv($file);
    }

    private function uploadXlsx($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the Excel file: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $data  = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return back()->with('error', 'The Excel file appears to be empty.');
        }

        $headerRow      = $data[0];
        $subcatNames    = array_slice($headerRow, 1);
        $subcatNames    = array_filter($subcatNames, fn($b) => trim((string)$b) !== '');
        $subcatNames    = array_values($subcatNames);

        $rows          = [];
        $now           = now();
        $importedCount = 0;

        foreach (array_slice($data, 1) as $line) {
            $supplierName = trim((string)($line[0] ?? ''));
            if (empty($supplierName) || is_numeric($supplierName)) {
                continue;
            }

            foreach ($subcatNames as $idx => $subcat) {
                $subcat = trim((string)$subcat);
                if (empty($subcat)) continue;
                $count = (int)($line[$idx + 1] ?? 0);
                if ($count > 0) {
                    $rows[] = [
                        'supplier_name'    => $supplierName,
                        'subcategory_name' => $subcat,
                        'count'            => $count,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                    $importedCount++;
                }
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'No valid data found in the Excel file.');
        }

        DB::table('supplier_subcategories')->truncate();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supplier_subcategories')->insert($chunk);
        }

        SupplierSubcategoryService::clearCache();

        return back()->with('success', "{$importedCount} subcategory-supplier entries imported successfully.");
    }

    private function uploadCsv($file)
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Could not open the uploaded file.');
        }

        $firstLine  = fgets($handle);
        rewind($handle);
        $tabCount   = substr_count($firstLine, "\t");
        $commaCount = substr_count($firstLine, ',');
        $delimiter  = $tabCount > $commaCount ? "\t" : ',';

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'Empty file.');
        }

        $subcatNames = array_slice($headers, 1);
        $subcatNames = array_filter($subcatNames, fn($b) => trim($b) !== '');
        $subcatNames = array_values($subcatNames);

        $rows          = [];
        $now           = now();
        $importedCount = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line) || empty(trim($line[0] ?? ''))) continue;
            $supplierName = trim($line[0]);
            if (empty($supplierName) || is_numeric($supplierName)) continue;

            foreach ($subcatNames as $idx => $subcat) {
                $subcat = trim($subcat);
                if (empty($subcat)) continue;
                $count = (int)($line[$idx + 1] ?? 0);
                if ($count > 0) {
                    $rows[] = [
                        'supplier_name'    => $supplierName,
                        'subcategory_name' => $subcat,
                        'count'            => $count,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                    $importedCount++;
                }
            }
        }

        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'No valid data found in the file.');
        }

        DB::table('supplier_subcategories')->truncate();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supplier_subcategories')->insert($chunk);
        }

        SupplierSubcategoryService::clearCache();

        return back()->with('success', "{$importedCount} subcategory-supplier entries imported successfully.");
    }
}
