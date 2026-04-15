<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierBrandService;

class SupplierBrandController extends Controller
{
    public function index()
    {
        // Get all unique suppliers and brands from the table
        $rows = DB::table('supplier_brands')->get();

        // Build matrix: supplier → brand → count
        $matrix = [];
        $brands  = [];
        $suppliers = [];

        foreach ($rows as $row) {
            $matrix[$row->supplier_name][$row->brand_name] = $row->count;
            $brands[$row->brand_name] = true;
            $suppliers[$row->supplier_name] = true;
        }

        ksort($brands);
        ksort($suppliers);
        $brands    = array_keys($brands);
        $suppliers = array_keys($suppliers);

        $isEmpty = empty($rows->toArray());

        return view('supplier_brands.index', compact('matrix', 'brands', 'suppliers', 'isEmpty'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Could not open the uploaded file.');
        }

        // Read header row — first row contains brand names
        $headerRow = fgetcsv($handle, 0, "\t"); // try tab first
        if (!$headerRow || count($headerRow) < 2) {
            rewind($handle);
            $headerRow = fgetcsv($handle, 0, ',');
        }

        if (!$headerRow) {
            fclose($handle);
            return back()->with('error', 'Could not read the header row.');
        }

        // Detect delimiter by checking header column count
        // Re-open with correct delimiter
        fclose($handle);
        $handle = fopen($file->getRealPath(), 'r');

        // Sniff delimiter from first line
        $firstLine = fgets($handle);
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

        // headers[0] = "Main SMOs (China)" or supplier label — skip it
        // headers[1..N] = brand names
        $brandNames = array_slice($headers, 1);
        // Remove empty trailing headers
        $brandNames = array_filter($brandNames, fn($b) => trim($b) !== '');
        $brandNames = array_values($brandNames);

        $rows = [];
        $now  = now();
        $importedCount = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line) || empty(trim($line[0] ?? ''))) {
                continue;
            }

            $supplierName = trim($line[0]);

            // Skip rows that look like formula/summary rows (no supplier name or starts with space)
            if (empty($supplierName) || is_numeric($supplierName)) {
                continue;
            }

            foreach ($brandNames as $idx => $brand) {
                $brand = trim($brand);
                if (empty($brand)) {
                    continue;
                }
                $count = (int) ($line[$idx + 1] ?? 0);
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
        // Insert in chunks to avoid query size limits
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('supplier_brands')->insert($chunk);
        }

        // Clear the in-memory cache so next request picks up new data
        SupplierBrandService::clearCache();

        return back()->with('success', "{$importedCount} brand-supplier entries imported successfully.");
    }
}
