<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    public function index()
    {
        $contacts = DB::table('contacts')
            ->leftJoin('companies', 'contacts.company_id', '=', 'companies.id')
            ->leftJoin('countries', 'contacts.country_id', '=', 'countries.id')
            ->select(
                'contacts.*',
                'companies.name as company_name',
                'countries.name as country_name'
            )
            ->orderBy('contacts.created_at', 'desc')
            ->paginate(25);

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        $companies = DB::table('companies')
            ->select('id','name')
            ->orderBy('name')
            ->get();

        $countries = DB::table('countries')
            ->where('is_active', true)
            ->select('id','name')
            ->orderBy('name')
            ->get();

        return view('contacts.create', compact(
            'companies',
            'countries'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'company_id' => 'nullable',
        'country_id' => 'nullable',
        'title' => 'nullable|string|max:255'
        ]);

        DB::table('contacts')->insert([
        'firstname' => $request->firstname,
        'lastname' => $request->lastname,
        'email' => $request->email,
        'phone' => $request->phone,
        'company_id' => $request->company_id,
        'country_id' => $request->country_id,
        'title' => $request->title,
        'created_at' => now(),
        'created_by' => auth()->id()
        ]);

        return redirect()->route('contacts.index')
        ->with('success', 'Contact created successfully.');
    }


    public function edit($id)
{
    $contact = DB::table('contacts')->where('id', $id)->first();

    if (!$contact) {
        abort(404);
    }

    $companies = DB::table('companies')
        ->select('id','name')
        ->orderBy('name')
        ->get();

    $countries = DB::table('countries')
        ->where('is_active', true)
        ->select('id','name')
        ->orderBy('name')
        ->get();

    return view('contacts.edit', compact(
        'contact',
        'companies',
        'countries'
    ));
}

public function update(Request $request, $id)
{
    $request->validate([
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'company_id' => 'nullable',
        'country_id' => 'nullable',
        'title' => 'nullable|string|max:255'
    ]);

    DB::table('contacts')
        ->where('id', $id)
        ->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_id' => $request->company_id,
            'country_id' => $request->country_id,
            'title' => $request->title,
            'updated_at' => now(),
            'updated_by' => auth()->id()
        ]);

    return redirect()->route('contacts.index')
        ->with('success', 'Contact updated successfully.');
}
}
