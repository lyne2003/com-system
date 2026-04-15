<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmoSupplierController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('smo_suppliers')
            ->orderBy('name')
            ->get();

        return view('smo_suppliers.index', compact('suppliers'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Could not open the uploaded file.');
        }

        $rows = [];
        $lineNumber = 0;
        $now = now();

        while (($line = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;

            // Skip header row(s): if first cell is not numeric and not a supplier name we recognise
            if ($lineNumber === 1) {
                // Check if first column looks like a header (non-numeric name column)
                if (strtolower(trim($line[0] ?? '')) === 'name' ||
                    strtolower(trim($line[0] ?? '')) === 'supplier') {
                    continue;
                }
            }

            // Expect: name, active_count, passive_count
            // Also handle the Excel format with a leading ID column: id, name, active, passive
            $cols = array_map('trim', $line);

            // Remove empty rows
            if (empty($cols[0]) && empty($cols[1] ?? '')) {
                continue;
            }

            // Detect if first column is numeric (ID column from Excel export)
            if (is_numeric($cols[0]) && isset($cols[1]) && !is_numeric($cols[1])) {
                // Format: id, name, active, passive
                $name         = $cols[1] ?? '';
                $activeCount  = (int) ($cols[2] ?? 0);
                $passiveCount = (int) ($cols[3] ?? 0);
            } else {
                // Format: name, active, passive
                $name         = $cols[0] ?? '';
                $activeCount  = (int) ($cols[1] ?? 0);
                $passiveCount = (int) ($cols[2] ?? 0);
            }

            if (empty($name)) {
                continue;
            }

            $rows[] = [
                'name'          => $name,
                'active_count'  => $activeCount,
                'passive_count' => $passiveCount,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'No valid rows found in the CSV file. Please check the format.');
        }

        // Replace all existing data
        DB::table('smo_suppliers')->truncate();
        DB::table('smo_suppliers')->insert($rows);

        return back()->with('success', count($rows) . ' suppliers imported successfully.');
    }
}
