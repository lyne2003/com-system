<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('opportunities')
            ->leftJoin('companies', 'opportunities.company_id', '=', 'companies.id')
            ->leftJoin('countries', 'opportunities.country_id', '=', 'countries.id')
            ->select(
            'opportunities.*',
            'companies.name as company_name',
            'countries.name as country_name'
            );

        // Filters
        if ($request->company_id) {
            $query->where('opportunities.company_id', $request->company_id);
        }

        if ($request->status) {
            $query->where('opportunities.status', $request->status);
        }

        $opportunities = $query->orderBy('opportunities.created_at', 'desc')->paginate(20);

        // Get only products and activities for the fetched opportunities
        $opportunityIds = $opportunities->pluck('id')->toArray();

        $products = DB::table('products')
            ->whereIn('opportunity_id', $opportunityIds)
            ->get()
            ->groupBy('opportunity_id');

        $activities = DB::table('activities')
            ->whereIn('opportunity_id', $opportunityIds)
            ->get()
            ->groupBy('opportunity_id');

        return view('opportunities.index', [
        'opportunities' => $opportunities,
        'products' => $products,
        'activities' => $activities
        ]);
    }

public function create()
{
    $companies = DB::table('companies')->get();
    $countries = DB::table('countries')->get();
    $sales = DB::table('users')->get();
    $engineers = DB::table('users')->get();

    return view('opportunities.create', compact('companies','countries','sales','engineers'));
}

public function store(Request $request)
{
    DB::beginTransaction();

    try {

        // Insert Opportunity
        $opportunity_id = DB::table('opportunities')->insertGetId([
            'company_id' => $request->company_id,
            'country_id' => $request->country_id,
            'project_application' => $request->project_application,
            'status' => $request->status,

            'assigned_sales_id' => $request->assigned_sales_id,
            'assigned_engineer_id' => $request->assigned_engineer_id,
            'notes' => $request->notes,

            'closed_won_percentage' => $request->closed_won_percentage,
            'closed_lost_reason' => $request->closed_lost_reason,

            'created_at' => now(),
            'updated_at' => now()
        ]);


        // Insert Products
        if ($request->products) {

            foreach ($request->products as $product) {

                // skip empty rows
                if(empty($product['part_number'])) {
                    continue;
                }

                DB::table('products')->insert([
                    'opportunity_id' => $opportunity_id,
                    'part_number' => $product['part_number'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'moq' => $product['moq'],
                    'mpq' => $product['mpq'],
                    'lead_time' => $product['lead_time'],
                    'date_code' => $product['date_code'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }


        // Insert Activities
        if ($request->activities) {

            foreach ($request->activities as $activity) {

                // skip empty rows
                if(empty($activity['name'])) {
                    continue;
                }

                DB::table('activities')->insert([
                    'opportunity_id' => $opportunity_id,
                    'name' => $activity['name'],
                    'activity_date' => $activity['activity_date'],
                    'status' => $activity['status'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }


        DB::commit();

        return redirect()->route('opportunities.index');

    } catch (\Exception $e) {
        dd($e->getMessage());

        DB::rollback();
        return back()->with('error', $e->getMessage());
    }
}

public function edit($id)
{
    $opportunity = DB::table('opportunities')->where('id', $id)->first();

    $companies = DB::table('companies')->get();
    $countries = DB::table('countries')->get();

    $products = DB::table('products')
        ->where('opportunity_id', $id)
        ->get();

    $activities = DB::table('activities')
        ->where('opportunity_id', $id)
        ->get();

    $sales = DB::table('users')->get();
    $engineers = DB::table('users')->get();

    return view('opportunities.edit', compact(
        'opportunity',
        'companies',
        'countries',
        'products',
        'activities',
        'sales',
        'engineers'
    ));
}

public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {

DB::table('opportunities')
->where('id',$id)
->update([
    'company_id' => $request->company_id,
    'country_id' => $request->country_id,
    'project_application' => $request->project_application,
    'status' => $request->status,

    'assigned_sales_id' => $request->assigned_sales_id,
    'assigned_engineer_id' => $request->assigned_engineer_id,
    'notes' => $request->notes,

    'closed_won_percentage' => $request->closed_won_percentage,
    'closed_lost_reason' => $request->closed_lost_reason,

    'updated_at' => now()
]);


        // delete old products
        DB::table('products')->where('opportunity_id',$id)->delete();

        if($request->products){

            foreach($request->products as $product){

                if(empty($product['part_number'])) continue;

                DB::table('products')->insert([
                    'opportunity_id'=>$id,
                    'part_number'=>$product['part_number'],
                    'quantity'=>$product['quantity'],
                    'unit_price'=>$product['unit_price'],
                    'moq'=>$product['moq'],
                    'mpq'=>$product['mpq'],
                    'lead_time'=>$product['lead_time'],
                    'date_code'=>$product['date_code'],
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

        }


        // delete old activities
        DB::table('activities')->where('opportunity_id',$id)->delete();

        if($request->activities){

            foreach($request->activities as $activity){

                if(empty($activity['name'])) continue;

                DB::table('activities')->insert([
                    'opportunity_id'=>$id,
                    'name'=>$activity['name'],
                    'activity_date'=>$activity['activity_date'],
                    'status'=>$activity['status'],
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

        }

        DB::commit();

        return redirect()->route('opportunities.index');

    } catch (\Exception $e) {

        DB::rollback();
        return back()->with('error',$e->getMessage());

    }
}
}