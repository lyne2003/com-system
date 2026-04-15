<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierBrandService;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplierBrandController extends Controller
{
    public function index()
    {
        // Try DB first
        $rows = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('supplier_brands')) {
                $rows = DB::table('supplier_brands')->get()->toArray();
            }
        } catch (\Throwable $e) {
            $rows = [];
        }

        $matrix    = [];
        $brands    = [];
        $suppliers = [];

        if (!empty($rows)) {
            // Build from DB
            foreach ($rows as $row) {
                $matrix[$row->supplier_name][$row->brand_name] = $row->count;
                $brands[$row->brand_name]       = true;
                $suppliers[$row->supplier_name] = true;
            }
        } else {
            // Fall back to hardcoded data from SupplierBrandService
            $fallback = \App\Services\SupplierBrandService::getFallbackData();
            foreach ($fallback as $supplier => $brandMap) {
                foreach ($brandMap as $brand => $count) {
                    if ($count > 0) {
                        $matrix[$supplier][$brand] = $count;
                        $brands[$brand]     = true;
                        $suppliers[$supplier] = true;
                    }
                }
            }
        }

        $brands    = array_keys($brands);
        $suppliers = array_keys($suppliers);
        sort($brands);     // A→Z
        sort($suppliers);  // A→Z

        $isEmpty = empty($matrix);

        return view('supplier_brands.index', compact('matrix', 'brands', 'suppliers', 'isEmpty'));
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

        // CSV / TXT path
        return $this->uploadCsv($file);
    }

    private function uploadXlsx($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the Excel file: ' . $e->getMessage());
        }

        // Use the first sheet
        $sheet = $spreadsheet->getActiveSheet();
        $data  = $sheet->toArray(null, true, true, false); // 0-indexed rows

        if (empty($data)) {
            return back()->with('error', 'The Excel file appears to be empty.');
        }

        // Row 0 = header (brand names), col 0 = "Main SMOs (China)" label
        $headerRow  = $data[0];
        $brandNames = array_slice($headerRow, 1);
        // Remove empty trailing brand names
        $brandNames = array_filter($brandNames, fn($b) => trim((string)$b) !== '');
        $brandNames = array_values($brandNames);

        $rows          = [];
        $now           = now();
        $importedCount = 0;

        foreach (array_slice($data, 1) as $line) {
            $supplierName = trim((string)($line[0] ?? ''));
            if (empty($supplierName) || is_numeric($supplierName)) {
                continue;
            }

            foreach ($brandNames as $idx => $brand) {
                $brand = trim((string)$brand);
                if (empty($brand)) {
                    continue;
                }
                $count = (int)($line[$idx + 1] ?? 0);
                if ($count > 0) {
                    $rows[] = [
                        'supplier_name' => $supplierName,
                        'brand_name'    => $brand,
                        'count'         => $count,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                    $importedCount++;
                }
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'No valid data found in the Excel file. Make sure the format matches the Supplier-Brands sheet.');
        }

        DB::table('supplier_brands')->truncate();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supplier_brands')->insert($chunk);
        }

        SupplierBrandService::clearCache();

        return back()->with('success', "{$importedCount} brand-supplier entries imported successfully.");
    }

    private function uploadCsv($file)
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Could not open the uploaded file.');
        }

        // Sniff delimiter from first line
        $firstLine  = fgets($handle);
        rewind($handle);
        $tabCount   = substr_count($firstLine, "\t");
        $commaCount = substr_count($firstLine, ',');
        $delimiter  = $tabCount > $commaCount ? "\t" : ',';

        // Read header
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'Empty file.');
        }

        $brandNames = array_slice($headers, 1);
        $brandNames = array_filter($brandNames, fn($b) => trim($b) !== '');
        $brandNames = array_values($brandNames);

        $rows          = [];
        $now           = now();
        $importedCount = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line) || empty(trim($line[0] ?? ''))) {
                continue;
            }

            $supplierName = trim($line[0]);
            if (empty($supplierName) || is_numeric($supplierName)) {
                continue;
            }

            foreach ($brandNames as $idx => $brand) {
                $brand = trim($brand);
                if (empty($brand)) {
                    continue;
                }
                $count = (int)($line[$idx + 1] ?? 0);
                if ($count > 0) {
                    $rows[] = [
                        'supplier_name' => $supplierName,
                        'brand_name'    => $brand,
                        'count'         => $count,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                    $importedCount++;
                }
            }
        }

        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'No valid data found in the file. Make sure the format matches the Supplier-Brands sheet.');
        }

        DB::table('supplier_brands')->truncate();
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supplier_brands')->insert($chunk);
        }

        SupplierBrandService::clearCache();

        return back()->with('success', "{$importedCount} brand-supplier entries imported successfully.");
    }
}
