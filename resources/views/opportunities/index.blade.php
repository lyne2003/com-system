<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Opportunities
    </h2>

    <a href="{{ route('opportunities.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
        + Add Opportunity
    </a>
</div>
</x-slot>


<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

@foreach($opportunities as $opportunity)

<div class="bg-white shadow rounded-lg p-6 mb-6">

    {{-- Opportunity Header --}}
    <div class="mb-4">
        <h3 class="text-lg font-bold">
            {{ $opportunity->company_name ?? 'No Company' }}
        </h3>

        <p class="text-gray-600">
            Country: {{ $opportunity->country_name ?? 'No Country' }}
        </p>

        <p class="text-gray-600">
            Project Application: {{ $opportunity->project_application }}
        </p>

        <p class="text-sm text-gray-500">
            Status: {{ $opportunity->status }}
        </p>

        {{-- Closed Won --}}
        @if($opportunity->status === 'Closed Won')
        <p class="text-green-600 font-semibold mt-1">
            Closed Won %: {{ $opportunity->closed_won_percentage }}%
        </p>
        @endif

        {{-- Closed Lost --}}
        @if($opportunity->status === 'Closed Lost')
        <p class="text-red-600 font-semibold mt-1">
            Closed Lost Reason: {{ $opportunity->closed_lost_reason }}
        </p>
        @endif
    </div>

    {{-- EDIT BUTTON --}}
<a href="{{ route('opportunities.edit', $opportunity->id) }}"
class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-4 py-2 rounded shadow">

Edit

</a>


    <div class="grid grid-cols-2 gap-8">

        {{-- PRODUCTS --}}
        <div>

            <h4 class="font-semibold mb-3 text-gray-800">Products</h4>

            @if(isset($products[$opportunity->id]) && count($products[$opportunity->id]) > 0)

            <div class="overflow-x-auto">

                <table class="min-w-full border border-gray-200 text-sm">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 border text-left">Part Number</th>
                            <th class="px-3 py-2 border text-left">Qty</th>
                            <th class="px-3 py-2 border text-left">Unit Price</th>
                            <th class="px-3 py-2 border text-left">MOQ</th>
                            <th class="px-3 py-2 border text-left">MPQ</th>
                            <th class="px-3 py-2 border text-left">Lead Time</th>
                            <th class="px-3 py-2 border text-left">Date Code</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($products[$opportunity->id] as $product)

                        <tr class="hover:bg-gray-50">

                            <td class="px-3 py-2 border font-medium">
                                {{ $product->part_number }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->quantity }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->unit_price }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->moq }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->mpq }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->lead_time }}
                            </td>

                            <td class="px-3 py-2 border">
                                {{ $product->date_code }}
                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            @else

            <p class="text-gray-400 text-sm">
                No products added.
            </p>

            @endif

        </div>


        {{-- ACTIVITIES --}}
        <div>

            <h4 class="font-semibold mb-3 text-gray-800">Activities</h4>

            @if(isset($activities[$opportunity->id]) && count($activities[$opportunity->id]) > 0)

            <div class="bg-gray-50 rounded-lg border overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">Activity</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($activities[$opportunity->id] as $activity)

                        <tr class="border-t hover:bg-gray-50">

                            <td class="px-4 py-2 font-medium">
                                {{ $activity->name }}
                            </td>

                            <td class="px-4 py-2 text-gray-600">
                                {{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y') }}
                            </td>

                            <td class="px-4 py-2">

                                <span class="px-2 py-1 rounded text-xs font-semibold
                                @if($activity->status == 'Completed') bg-green-100 text-green-700
                                @elseif($activity->status == 'Pending') bg-yellow-100 text-yellow-700
                                @else bg-gray-200 text-gray-700
                                @endif">

                                    {{ $activity->status }}

                                </span>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            @else

            <p class="text-gray-400 text-sm">
                No activities recorded.
            </p>

            @endif

        </div>

    </div>

</div>

@endforeach

</div>
</div>

</x-app-layout>