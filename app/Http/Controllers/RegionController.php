<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('regions');

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === '1');
        }

        $regions = $query->orderBy('name')->paginate(25);

        return view('regions.index', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        DB::table('regions')->insert([
            'id'         => \Illuminate\Support\Str::uuid(),
            'name'       => $request->name,
            'is_active'  => true,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('regions.index')
            ->with('success', 'Region "' . $request->name . '" added successfully.');
    }

    public function edit($id)
    {
        $region = DB::table('regions')->where('id', $id)->first();

        if (!$region) {
            abort(404);
        }

        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        DB::table('regions')->where('id', $id)->update([
            'name'       => $request->name,
            'is_active'  => $request->has('is_active') ? true : false,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('regions.index')
            ->with('success', 'Region updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('regions')->where('id', $id)->delete();

        return redirect()->route('regions.index')
            ->with('success', 'Region deleted successfully.');
    }
}
