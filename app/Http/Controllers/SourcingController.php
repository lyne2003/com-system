<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MouserService;
use App\Services\DigiKeyService;
use App\Services\TiService;

class SourcingController extends Controller
{
    /**
     * Show sourcing results for an RFQ.
     */
    public function show($rfqId)
    {
        $rfq = DB::table('rfqs')
            ->leftJoin('companies', 'rfqs.client_id', '=', 'companies.id')
            ->select('rfqs.*', 'companies.name as client_name')
            ->where('rfqs.id', $rfqId)
            ->first();

        if (!$rfq) {
            abort(404);
        }

        $items = DB::table('items')
            ->where('rfq_id', $rfqId)
            ->orderBy('line_number')
            ->get();

        // Load sourcing results grouped by item_id then supplier
        $results = DB::table('sourcing_results')
            ->where('rfq_id', $rfqId)
            ->orderBy('supplier')
            ->get()
            ->groupBy('item_id');

        return view('rfqs.source', compact('rfq', 'items', 'results'));
    }

    /**
     * Trigger sourcing for all items in an RFQ (called after store).
     */
    public function run($rfqId)
    {
        $rfq = DB::table('rfqs')->where('id', $rfqId)->first();
        if (!$rfq) {
            abort(404);
        }

        $items = DB::table('items')
            ->where('rfq_id', $rfqId)
            ->orderBy('line_number')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('rfqs.source.show', $rfqId)
                ->with('info', 'No items to source.');
        }

        // Delete old results for this RFQ
        DB::table('sourcing_results')->where('rfq_id', $rfqId)->delete();

        $mouser  = new MouserService();
        $digikey = new DigiKeyService();
        $ti      = new TiService();

        foreach ($items as $item) {
            $partNumber = trim($item->partnumber ?? '');
            $qty        = (int) ($item->qty ?? 1);

            if (empty($partNumber)) {
                continue;
            }

            $suppliers = [
                $mouser->search($partNumber, $qty),
                $digikey->search($partNumber, $qty),
                $ti->search($partNumber, $qty),
            ];

            foreach ($suppliers as $result) {
                DB::table('sourcing_results')->insert([
                    'id'             => \Illuminate\Support\Str::uuid(),
                    'rfq_id'         => $rfqId,
                    'item_id'        => $item->id,
                    'partnumber'     => $partNumber,
                    'supplier'       => $result['supplier'] ?? 'unknown',
                    'status'         => $result['status'] ?? 'error',
                    'description'    => $result['description'] ?? null,
                    'manufacturer'   => $result['manufacturer'] ?? null,
                    'manufacturer_pn'=> $result['manufacturer_pn'] ?? null,
                    'unit_price'     => $result['unit_price'] ?? null,
                    'availability'   => $result['availability'] ?? null,
                    'stock_status'   => $result['stock_status'] ?? null,
                    'lead_time'      => $result['lead_time'] ?? null,
                    'moq'            => $result['moq'] ?? null,
                    'package_type'   => $result['package_type'] ?? null,
                    'package_qty'    => $result['package_qty'] ?? null,
                    'datasheet_url'  => $result['datasheet_url'] ?? null,
                    'category'       => $result['category'] ?? null,
                    'raw_response'   => $result['raw_response'] ?? null,
                    'sourced_at'     => now(),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // Small delay between items to avoid rate limits
            usleep(300000); // 300ms
        }

        return redirect()->route('rfqs.source.show', $rfqId)
            ->with('success', 'Sourcing completed for all items.');
    }
}
