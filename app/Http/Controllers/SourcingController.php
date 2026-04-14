<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SourcingJob;

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
     * AJAX: return current sourcing status + progress counts.
     */
    public function status($rfqId)
    {
        $rfq = DB::table('rfqs')->where('id', $rfqId)->first();
        if (!$rfq) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $totalItems = DB::table('items')->where('rfq_id', $rfqId)->count();
        $sourcedItems = DB::table('sourcing_results')
            ->where('rfq_id', $rfqId)
            ->distinct('item_id')
            ->count('item_id');

        return response()->json([
            'status'        => $rfq->sourcing_status ?? 'idle',
            'total'         => $totalItems,
            'sourced'       => $sourcedItems,
            'percent'       => $totalItems > 0 ? round(($sourcedItems / $totalItems) * 100) : 0,
        ]);
    }

    /**
     * Dispatch sourcing jobs for all items in an RFQ.
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

        // Mark as processing
        DB::table('rfqs')->where('id', $rfqId)->update([
            'sourcing_status' => 'processing',
            'updated_at'      => now(),
        ]);

        $totalItems = $items->count();

        // Dispatch one job per item
        foreach ($items as $item) {
            SourcingJob::dispatch($rfqId, $item, $totalItems);
        }

        return redirect()->route('rfqs.source.show', $rfqId)
            ->with('info', 'Sourcing started in the background. Results will appear as they come in.');
    }
}
