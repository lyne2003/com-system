<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InquiryNumberService;

class RfqController extends Controller
{
    /**
     * AJAX: preview the next inquiry number for a given client.
     * Does NOT increment the counter.
     */
    public function inquiryNumberPreview(Request $request)
    {
        $clientId = $request->query('client_id');

        if (!$clientId) {
            return response()->json(['inquiry_n' => null]);
        }

        $service   = new InquiryNumberService();
        $nextNumber = $service->peekNextNumber($clientId);

        return response()->json(['inquiry_n' => $nextNumber]);
    }

    public function edit($id)
    {
        $rfq = DB::table('rfqs')->where('id', $id)->first();

        if (!$rfq) {
            abort(404);
        }

        $items = DB::table('items')
            ->where('rfq_id', $id)
            ->orderBy('line_number')
            ->get();

        $companies = DB::table('companies')
            ->join('types', 'companies.type_id', '=', 'types.id')
            ->where('types.name', 'Client')
            ->leftJoin('regions', 'companies.region_id', '=', 'regions.id')
            ->select('companies.id', 'companies.name', 'regions.name as region_name')
            ->orderBy('companies.name')
            ->get();

        $manufacturers = DB::table('manufacturers')->where('is_active', true)->select('id', 'name')->orderBy('name')->get();

        return view('rfqs.edit', compact('rfq', 'items', 'companies', 'manufacturers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
            'date'      => 'nullable|date',
            'client_id' => 'nullable|uuid',
            'priority'  => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            DB::table('rfqs')->where('id', $id)->update([
                'reference'           => $request->reference,
                'inquiry_n'           => $request->inquiry_n,
                'date'                => $request->date,
                'client_id'           => $request->client_id ?: null,
                'priority'            => $request->priority,
                'notes_to_purchasing' => $request->notes_to_purchasing,
                'notes_to_elias'      => $request->notes_to_elias,
                'updated_by'          => auth()->id(),
                'updated_at'          => now(),
            ]);

            // Delete existing items and re-insert
            DB::table('items')->where('rfq_id', $id)->delete();

            if ($request->items) {
                $lineNumber = 1;
                foreach ($request->items as $item) {
                    if (empty($item['partnumber'])) {
                        continue;
                    }

                    DB::table('items')->insert([
                        'rfq_id'          => $id,
                        'line_number'     => $item['line_number'] ?? $lineNumber,
                        'overallcode'     => $item['overallcode'] ?? null,
                        'partnumber'      => $item['partnumber'],
                        'qty'             => $item['qty'] ?? null,
                        'uom'             => $item['uom'] ?? null,
                        'target_price'    => $item['target_price'] ?? null,
                        'manufacturer_id' => ($item['manufacturer_id'] ?? null) ?: null,
                        'created_by'      => auth()->id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    $lineNumber++;
                }
            }

            DB::commit();

            return redirect()->route('rfqs.index')->with('success', 'RFQ updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table('items')->where('rfq_id', $id)->delete();
            DB::table('rfqs')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $query = DB::table('rfqs')
            ->leftJoin('companies', 'rfqs.client_id', '=', 'companies.id')
            ->leftJoin('regions', 'companies.region_id', '=', 'regions.id')
            ->select(
                'rfqs.*',
                'companies.name as client_name',
                'regions.name as client_region'
            );

        // Search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rfqs.reference', 'ilike', "%{$search}%")
                  ->orWhere('rfqs.inquiry_n', 'ilike', "%{$search}%")
                  ->orWhere('companies.name', 'ilike', "%{$search}%");
            });
        }

        // Priority filter
        if ($request->priority) {
            $query->where('rfqs.priority', $request->priority);
        }

        // Client filter
        if ($request->client_id) {
            $query->where('rfqs.client_id', $request->client_id);
        }

        $rfqs = $query->orderBy('rfqs.created_at', 'desc')->paginate(20);

        // Load items only for current page RFQs
        $rfqIds = $rfqs->pluck('id')->toArray();

        $items = DB::table('items')
            ->leftJoin('manufacturers', 'items.manufacturer_id', '=', 'manufacturers.id')
            ->select(
                'items.*',
                'manufacturers.name as manufacturer_name'
            )
            ->whereIn('items.rfq_id', $rfqIds)
            ->orderBy('items.line_number')
            ->get()
            ->groupBy('rfq_id');

        // For filter dropdowns
        $clients = DB::table('companies')->select('id', 'name')->orderBy('name')->get();

        return view('rfqs.index', compact('rfqs', 'items', 'clients'));
    }

    public function create()
    {
        // Only companies whose type is "Client"
        $companies = DB::table('companies')
            ->join('types', 'companies.type_id', '=', 'types.id')
            ->where('types.name', 'Client')
            ->leftJoin('regions', 'companies.region_id', '=', 'regions.id')
            ->select('companies.id', 'companies.name', 'regions.name as region_name')
            ->orderBy('companies.name')
            ->get();

        $manufacturers = DB::table('manufacturers')->where('is_active', true)->select('id', 'name')->orderBy('name')->get();

        return view('rfqs.create', compact('companies', 'manufacturers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
            'date'      => 'nullable|date',
            'client_id' => 'required|uuid',
            'priority'  => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            // Auto-assign inquiry number based on client's country/region
            $inquiryNumber = null;
            if ($request->client_id) {
                $service = new InquiryNumberService();
                $inquiryNumber = $service->assignNextNumber($request->client_id);
            }

            $rfq_id = DB::table('rfqs')->insertGetId([
                'reference'           => $request->reference,
                'inquiry_n'           => $inquiryNumber ?? $request->inquiry_n,
                'date'                => $request->date,
                'client_id'           => $request->client_id ?: null,
                'priority'            => $request->priority,
                'notes_to_purchasing' => $request->notes_to_purchasing,
                'notes_to_elias'      => $request->notes_to_elias,
                'created_by'          => auth()->id(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            if ($request->items) {
                $lineNumber = 1;
                foreach ($request->items as $item) {
                    if (empty($item['partnumber'])) {
                        continue;
                    }

                    DB::table('items')->insert([
                        'rfq_id'          => $rfq_id,
                        'line_number'     => $item['line_number'] ?? $lineNumber,
                        'overallcode'     => $item['overallcode'] ?? null,
                        'partnumber'      => $item['partnumber'],
                        'qty'             => $item['qty'] ?? null,
                        'uom'             => $item['uom'] ?? null,
                        'target_price'    => $item['target_price'] ?? null,
                        'manufacturer_id' => ($item['manufacturer_id'] ?? null) ?: null,
                        'created_by'      => auth()->id(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    $lineNumber++;
                }
            }

            DB::commit();

            // Trigger sourcing automatically
            return redirect()->route('rfqs.source.run', $rfq_id);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
