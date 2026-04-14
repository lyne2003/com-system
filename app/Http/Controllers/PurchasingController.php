<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        // Join items → rfqs → sourcing_results (mouser only)
        $query = DB::table('items')
            ->join('rfqs', 'items.rfq_id', '=', 'rfqs.id')
            ->leftJoin('sourcing_results', function ($join) {
                $join->on('sourcing_results.item_id', '=', 'items.id')
                     ->where('sourcing_results.supplier', '=', 'mouser');
            })
            ->select(
                'items.overallcode',
                'rfqs.inquiry_n as order_code',
                'rfqs.date',
                'items.partnumber',
                'sourcing_results.manufacturer_pn as mouser_part_number',
                'sourcing_results.unit_price as mouser_price',
                'sourcing_results.availability as mouser_stock',
                'sourcing_results.status as mouser_status'
            )
            ->orderBy('rfqs.date', 'desc')
            ->orderBy('items.line_number');

        // Search filter
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('items.partnumber', 'ilike', "%{$s}%")
                  ->orWhere('items.overallcode', 'ilike', "%{$s}%")
                  ->orWhere('rfqs.inquiry_n', 'ilike', "%{$s}%")
                  ->orWhere('sourcing_results.manufacturer_pn', 'ilike', "%{$s}%");
            });
        }

        $rows = $query->paginate(50)->withQueryString();

        return view('purchasing.index', compact('rows'));
    }
}
