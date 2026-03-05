<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = DB::table('companies')
            ->leftJoin('countries', 'companies.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'companies.region_id', '=', 'regions.id')
            ->leftJoin('types', 'companies.type_id', '=', 'types.id')
            ->leftJoin('industries', 'companies.industry_id', '=', 'industries.id')
            ->leftJoin('tiers', 'companies.tier_id', '=', 'tiers.id')
            ->leftJoin('users as sales', 'companies.assigned_sales_id', '=', 'sales.id')
            ->leftJoin('users as engineers', 'companies.assigned_engineer_id', '=', 'engineers.id')
            ->leftJoin('statuses', 'companies.status_id', '=', 'statuses.id')
            ->select(
                'companies.*',
                'countries.name as country_name',
                'regions.name as region_name',
                'types.name as type_name',
                'industries.name as industry_name',
                'tiers.name as tier_name',
                'sales.name as assigned_sales_name',
                'engineers.name as assigned_engineer_name',
                'statuses.name as status_name'
            )
            ->orderBy('companies.created_at', 'desc')
            ->get();

        return view('companies.index', compact('companies'));
    }
    public function create()
    {
        
        $regions = DB::table('regions')->where('is_active', true)->orderBy('name')->get();
        $countries = DB::table('country_region')
        ->join('countries', 'countries.id', '=', 'country_region.country_id')
        ->where('countries.is_active', true)
        ->select(
            'country_region.region_id',
            'countries.id',
            'countries.name'
        )
        ->get();
        $types = DB::table('types')->where('is_active', true)->orderBy('name')->get();
        $industries = DB::table('industries')->where('is_active', true)->orderBy('name')->get();
        $tiers = DB::table('tiers')->where('is_active', true)->orderBy('name')->get();
        $statuses = DB::table('statuses')->where('is_active', true)->orderBy('name')->get();
        $users = DB::table('users')->orderBy('name')->get();

        return view('companies.create', compact(
            'countries',
            'regions',
            'types',
            'industries',
            'tiers',
            'statuses',
            'users'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'website' => 'nullable|string',
        'country_id' => 'nullable',
        'region_id' => 'nullable',
        'type_id' => 'nullable',
        'industry_id' => 'nullable',
        'tier_id' => 'nullable',
        'assigned_sales_id' => 'nullable',
        'assigned_engineer_id' => 'nullable',
        'status_id' => 'nullable',
        ]);

        DB::table('companies')->insert([
        'name' => $request->name,
        'email' => $request->email,
        'website' => $request->website,
        'country_id' => $request->country_id,
        'region_id' => $request->region_id,
        'type_id' => $request->type_id,
        'industry_id' => $request->industry_id,
        'tier_id' => $request->tier_id,
        'assigned_sales_id' => $request->assigned_sales_id,
        'assigned_engineer_id' => $request->assigned_engineer_id,
        'status_id' => $request->status_id,
        'created_by' => auth()->id(),
        'created_at' => now()
        ]);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function getCountriesByRegion($regionId)
    {
        $countries = DB::table('countries')
            ->join('country_region', 'countries.id', '=', 'country_region.country_id')
            ->where('country_region.region_id', $regionId)
            ->where('countries.is_active', true)
            ->select('countries.id', 'countries.name')
            ->orderBy('countries.name')
            ->get();

        return response()->json($countries);
    }

    // public function edit($id)
    // {
    //     $company = DB::table('companies')->where('id', $id)->first();

    //     if (!$company) {
    //         abort(404);
    //     }

    //     $regions = DB::table('regions')
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     // 🔥 Load ONLY countries for selected region
    //     $countries = DB::table('countries')
    //         ->join('country_region', 'countries.id', '=', 'country_region.country_id')
    //         ->where('country_region.region_id', $company->region_id)
    //         ->where('countries.is_active', true)
    //         ->select('countries.id', 'countries.name')
    //         ->orderBy('countries.name')
    //         ->get();

    //     $types = DB::table('types')->where('is_active', true)->orderBy('name')->get();
    //     $industries = DB::table('industries')->where('is_active', true)->orderBy('name')->get();
    //     $tiers = DB::table('tiers')->where('is_active', true)->orderBy('name')->get();
    //     $statuses = DB::table('statuses')->where('is_active', true)->orderBy('name')->get();

    //     // ⚡ Only load necessary user fields
    //     $users = DB::table('users')
    //         ->select('id', 'name')
    //         ->orderBy('name')
    //             ->get();

    //     return view('companies.edit', compact(
    //         'company',
    //         'regions',
    //         'countries',
    //         'types',
    //         'industries',
    //         'tiers',
    //         'statuses',
    //         'users'
    //     ));
    // }

    public function edit($id)
{
    $company = DB::table('companies')->where('id', $id)->first();

    if (!$company) {
        abort(404);
    }

    $regions = DB::table('regions')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    // 🔥 Load ALL region-country combinations (pivot based)
    $countries = DB::table('country_region')
        ->join('countries', 'countries.id', '=', 'country_region.country_id')
        ->where('countries.is_active', true)
        ->select(
            'country_region.region_id',
            'countries.id',
            'countries.name'
        )
        ->get();

    $types = DB::table('types')->where('is_active', true)->orderBy('name')->get();
    $industries = DB::table('industries')->where('is_active', true)->orderBy('name')->get();
    $tiers = DB::table('tiers')->where('is_active', true)->orderBy('name')->get();
    $statuses = DB::table('statuses')->where('is_active', true)->orderBy('name')->get();
    $users = DB::table('users')->select('id', 'name')->orderBy('name')->get();

    return view('companies.edit', compact(
        'company',
        'regions',
        'countries',
        'types',
        'industries',
        'tiers',
        'statuses',
        'users'
    ));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('companies')
            ->where('id', $id)
            ->update([
            'name' => $request->name,
            'email' => $request->email,
            'website' => $request->website,
            'region_id' => $request->region_id,
            'country_id' => $request->country_id,
            'type_id' => $request->type_id,
            'industry_id' => $request->industry_id,
            'tier_id' => $request->tier_id,
            'assigned_sales_id' => $request->assigned_sales_id,
            'assigned_engineer_id' => $request->assigned_engineer_id,
            'status_id' => $request->status_id,
            'updated_at' => now(),
            'updated_by' => auth()->id(),
            ]);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }
}