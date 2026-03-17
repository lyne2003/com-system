<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('manufacturers');

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === '1');
        }

        $manufacturers = $query->orderBy('name')->paginate(25);

        return view('manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        // Redirect to index — adding is done via modal on index page
        return redirect()->route('manufacturers.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('manufacturers')->insert([
            'name'       => $request->name,
            'is_active'  => true,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer "' . $request->name . '" added successfully.');
    }

    public function edit($id)
    {
        $manufacturer = DB::table('manufacturers')->where('id', $id)->first();

        if (!$manufacturer) {
            abort(404);
        }

        return view('manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('manufacturers')->where('id', $id)->update([
            'name'       => $request->name,
            'is_active'  => $request->has('is_active') ? true : false,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('manufacturers')->where('id', $id)->delete();

        return redirect()->route('manufacturers.index')
            ->with('success', 'Manufacturer deleted successfully.');
    }
}
