<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupplierRecommendationService;
use App\Services\SupplierBrandService;
use App\Services\SupplierSubcategoryService;
use App\Services\SupplierTop1Service;
use App\Services\SupplierTop2Service;
use App\Services\SupplierTop3Service;

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
                'items.qty',
                // Manufacturer entered by user on the RFQ item
                'manufacturers.name as rfq_manufacturer',
                // Mouser part number
                'sr_mouser.manufacturer_pn as mouser_part_number',
                // Mouser manufacturer (for display / fallback)
                'sr_mouser.manufacturer as mouser_manufacturer',
                // Best manufacturer: RFQ user entry → mouser → digikey → ti
                DB::raw("COALESCE(manufacturers.name, sr_mouser.manufacturer, sr_digikey.manufacturer, sr_ti.manufacturer) as best_manufacturer"),
                // Best category: mouser → digikey → ti
                DB::raw("COALESCE(sr_mouser.category, sr_digikey.category, sr_ti.category) as best_category"),
                // Unit prices for volume calculation (mouser → digikey → ti)
                'sr_mouser.unit_price as mouser_unit_price',
                'sr_digikey.unit_price as digikey_unit_price',
                'sr_ti.unit_price as ti_unit_price'
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

            // Volume = unit_price × qty  (mouser → digikey → ti)
            $unitPrice = $row->mouser_unit_price ?? $row->digikey_unit_price ?? $row->ti_unit_price ?? null;
            $qty       = $row->qty ?? 0;
            $volume    = ($unitPrice !== null && $qty > 0) ? ((float)$unitPrice * (int)$qty) : null;
            $row->volume = $volume;

            // Build the flat list of all other suppliers (S1-S5, BrandS1-S4, SubcatS1-S4)
            $allSuppliers = array_merge(
                $row->recommended_suppliers,
                $row->brand_suppliers,
                $row->subcategory_suppliers
            );

            // Supplier Top 1
            $row->supplier_top1 = SupplierTop1Service::resolve(
                $row->best_manufacturer,
                $volume,
                $allSuppliers
            );

            // Supplier Top 2
            $row->supplier_top2 = SupplierTop2Service::resolve(
                $row->component_type,
                $row->best_manufacturer,
                $row->partnumber,
                $row->recommended_suppliers[0] ?? '',
                $row->recommended_suppliers[1] ?? '',
                $row->supplier_top1
            );

            // Supplier Top 3
            $row->supplier_top3 = SupplierTop3Service::resolve(
                $row->component_type,
                $volume,
                $row->partnumber,
                $allSuppliers,
                $row->supplier_top1,
                $row->supplier_top2
            );

            return $row;
        });

        return view('purchasing.index', compact('rows'));
    }
}
