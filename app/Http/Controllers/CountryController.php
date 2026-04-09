<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('countries');

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === '1');
        }

        $countries = $query->orderBy('name')->paginate(25);

        return view('countries.index', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        DB::table('countries')->insert([
            'id'         => \Illuminate\Support\Str::uuid(),
            'name'       => $request->name,
            'is_active'  => true,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('countries.index')
            ->with('success', 'Country "' . $request->name . '" added successfully.');
    }

    public function edit($id)
    {
        $country = DB::table('countries')->where('id', $id)->first();

        if (!$country) {
            abort(404);
        }

        return view('countries.edit', compact('country'));
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

        return redirect()->route('countries.index')
            ->with('success', 'Country updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('countries')->where('id', $id)->delete();

        return redirect()->route('countries.index')
            ->with('success', 'Country deleted successfully.');
    }
}
