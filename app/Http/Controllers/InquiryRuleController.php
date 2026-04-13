<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InquiryRuleController extends Controller
{
    public function index()
    {
        $rules = DB::table('inquiry_rules')
            ->orderBy('group_name')
            ->get();

        // Load countries for each rule
        $ruleIds = $rules->pluck('id')->toArray();
        $countries = DB::table('inquiry_rule_countries')
            ->whereIn('inquiry_rule_id', $ruleIds)
            ->orderBy('country_name')
            ->get()
            ->groupBy('inquiry_rule_id');

        return view('inquiry_rules.index', compact('rules', 'countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_name'  => 'required|string|max:100',
            'base_number' => 'required|integer|min:1',
        ]);

        $id = DB::table('inquiry_rules')->insertGetId([
            'group_name'  => $request->group_name,
            'base_number' => $request->base_number,
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Insert countries
        $countries = array_filter(array_map('trim', explode("\n", $request->countries ?? '')));
        foreach ($countries as $country) {
            if (!empty($country)) {
                DB::table('inquiry_rule_countries')->insert([
                    'inquiry_rule_id' => $id,
                    'country_name'    => $country,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        return redirect()->route('inquiry_rules.index')
            ->with('success', 'Rule "' . $request->group_name . '" created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'group_name'  => 'required|string|max:100',
            'base_number' => 'required|integer|min:1',
        ]);

        DB::table('inquiry_rules')->where('id', $id)->update([
            'group_name'  => $request->group_name,
            'base_number' => $request->base_number,
            'is_active'   => $request->has('is_active') ? true : false,
            'updated_at'  => now(),
        ]);

        // Replace countries
        DB::table('inquiry_rule_countries')->where('inquiry_rule_id', $id)->delete();

        $countries = array_filter(array_map('trim', explode("\n", $request->countries ?? '')));
        foreach ($countries as $country) {
            if (!empty($country)) {
                DB::table('inquiry_rule_countries')->insert([
                    'inquiry_rule_id' => $id,
                    'country_name'    => $country,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        return redirect()->route('inquiry_rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    public function destroy($id)
    {
        DB::table('inquiry_rule_countries')->where('inquiry_rule_id', $id)->delete();
        DB::table('inquiry_rules')->where('id', $id)->delete();

        return redirect()->route('inquiry_rules.index')
            ->with('success', 'Rule deleted successfully.');
    }
}
