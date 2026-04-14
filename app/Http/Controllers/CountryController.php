<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('countries')
            ->leftJoin('country_region', 'countries.id', '=', 'country_region.country_id')
            ->leftJoin('regions', 'country_region.region_id', '=', 'regions.id')
            ->select('countries.*', DB::raw("STRING_AGG(regions.name, ', ' ORDER BY regions.name) as region_names"))
            ->groupBy('countries.id', 'countries.name', 'countries.is_active', 'countries.created_by', 'countries.created_at', 'countries.updated_by', 'countries.updated_at');

        if ($request->search) {
            $search = $request->search;
            $query->where('countries.name', 'ilike', "%{$search}%");
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('countries.is_active', $request->is_active === '1');
        }

        $countries = $query->orderBy('countries.name')->paginate(25);

        $regions = DB::table('regions')->where('is_active', true)->orderBy('name')->get();

        return view('countries.index', compact('countries', 'regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $id = \Illuminate\Support\Str::uuid();

        DB::table('countries')->insert([
            'id'         => $id,
            'name'       => $request->name,
            'is_active'  => true,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        // Insert into country_region junction table
        foreach ((array) ($request->region_ids ?? []) as $regionId) {
            if (!empty($regionId)) {
                DB::table('country_region')->insertOrIgnore([
                    'id'         => \Illuminate\Support\Str::uuid(),
                    'country_id' => $id,
                    'region_id'  => $regionId,
                    'created_at' => now(),
                ]);
            }
        }

        return redirect()->route('countries.index')
            ->with('success', 'Country "' . $request->name . '" added successfully.');
    }

    public function edit($id)
    {
        $country = DB::table('countries')->where('id', $id)->first();

        if (!$country) {
            abort(404);
        }

        $regions = DB::table('regions')->where('is_active', true)->orderBy('name')->get();

        // Get currently assigned region IDs
        $assignedRegionIds = DB::table('country_region')
            ->where('country_id', $id)
            ->pluck('region_id')
            ->toArray();

        return view('countries.edit', compact('country', 'regions', 'assignedRegionIds'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        DB::table('countries')->where('id', $id)->update([
            'name'       => $request->name,
            'is_active'  => $request->has('is_active') ? true : false,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        // Replace region assignments
        DB::table('country_region')->where('country_id', $id)->delete();

        foreach ((array) ($request->region_ids ?? []) as $regionId) {
            if (!empty($regionId)) {
                DB::table('country_region')->insertOrIgnore([
                    'id'         => \Illuminate\Support\Str::uuid(),
                    'country_id' => $id,
                    'region_id'  => $regionId,
                    'created_at' => now(),
                ]);
            }
        }

        return redirect()->route('countries.index')
            ->with('success', 'Country updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('country_region')->where('country_id', $id)->delete();
        DB::table('countries')->where('id', $id)->delete();

        return redirect()->route('countries.index')
            ->with('success', 'Country deleted successfully.');
    }
}
