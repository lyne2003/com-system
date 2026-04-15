<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierRecommendationService;
use App\Services\SupplierBrandService;
use App\Services\SupplierSubcategoryService;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        // Join items → rfqs → manufacturers → sourcing_results
        $query = DB::table('items')
            ->join('rfqs', 'items.rfq_id', '=', 'rfqs.id')
            ->leftJoin('manufacturers', 'items.manufacturer_id', '=', 'manufacturers.id')
            ->leftJoin('sourcing_results as sr_mouser', function ($join) {
                $join->on('sr_mouser.item_id', '=', 'items.id')
                     ->where('sr_mouser.supplier', '=', 'mouser');
            })
            ->leftJoin('sourcing_results as sr_digikey', function ($join) {
                $join->on('sr_digikey.item_id', '=', 'items.id')
                     ->where('sr_digikey.supplier', '=', 'digikey');
            })
            ->leftJoin('sourcing_results as sr_ti', function ($join) {
                $join->on('sr_ti.item_id', '=', 'items.id')
                     ->where('sr_ti.supplier', '=', 'ti');
            })
            ->select(
                'items.id as item_id',
                'items.overallcode',
                'rfqs.inquiry_n as order_code',
                'rfqs.date',
                'items.partnumber',
                // Manufacturer entered by user on the RFQ item
                'manufacturers.name as rfq_manufacturer',
                // Mouser part number
                'sr_mouser.manufacturer_pn as mouser_part_number',
                // Mouser manufacturer (for display / fallback)
                'sr_mouser.manufacturer as mouser_manufacturer',
                // Best manufacturer: RFQ user entry → mouser → digikey → ti
                DB::raw("COALESCE(manufacturers.name, sr_mouser.manufacturer, sr_digikey.manufacturer, sr_ti.manufacturer) as best_manufacturer"),
                // Best category: mouser → digikey → ti
                DB::raw("COALESCE(sr_mouser.category, sr_digikey.category, sr_ti.category) as best_category")
            )
            ->orderBy('rfqs.date', 'desc')
            ->orderBy('rfqs.created_at', 'desc')
            ->orderBy('items.line_number');

        // Search filter
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('items.partnumber', 'ilike', "%{$s}%")
                  ->orWhere('items.overallcode', 'ilike', "%{$s}%")
                  ->orWhere('rfqs.inquiry_n', 'ilike', "%{$s}%")
                  ->orWhere('sr_mouser.manufacturer_pn', 'ilike', "%{$s}%");
            });
        }

        $rows = $query->paginate(50)->withQueryString();

        // Pre-compute top-5 suppliers for Active and Passive (cached, same for all rows)
        $activeSuppliers  = SupplierRecommendationService::getTopSuppliers('Active');
        $passiveSuppliers = SupplierRecommendationService::getTopSuppliers('Passive');

        // Attach component type + recommended suppliers to each row
        $rows->getCollection()->transform(function ($row) use ($activeSuppliers, $passiveSuppliers) {
            $type = SupplierRecommendationService::resolveType(
                $row->best_manufacturer,
                $row->best_category
            );
            $row->component_type = $type;
            $row->recommended_suppliers = match($type) {
                'Active'  => $activeSuppliers,
                'Passive' => $passiveSuppliers,
                default   => [],
            };
            // Brand-based suppliers: use RFQ manufacturer first, then fall back to Mouser manufacturer
            $brandName = $row->rfq_manufacturer ?? $row->mouser_manufacturer ?? '';
            $row->brand_suppliers = SupplierBrandService::getTopSuppliersForBrand($brandName, 4);

            // Subcategory-based suppliers: use best_category from sourcing results
            $row->subcategory_suppliers = SupplierSubcategoryService::getTopSuppliersForSubcategory(
                $row->best_category ?? '',
                4
            );
            return $row;
        });

        return view('purchasing.index', compact('rows'));
    }
}
