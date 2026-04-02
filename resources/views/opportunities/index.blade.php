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
<div class="mb-6">
<input
type="text"
id="searchInput"
placeholder="Search company, part number, country, project..."
class="w-full border rounded-lg px-4 py-2"
onkeyup="searchOpportunities()">
</div>
@foreach($opportunities as $opportunity)

<div x-data="{ open:false }" class="opportunity-card bg-white shadow rounded-lg mb-6">

    {{-- CLICKABLE HEADER --}}
    <div @click="open = !open" class="p-6 cursor-pointer">

        <h3 class="text-lg font-bold">
            {{ $opportunity->opportunity_name ?? ($opportunity->company_name ?? 'No Name') }}
        </h3>

        @if($opportunity->opportunity_name && $opportunity->company_name)
        <p class="text-sm text-gray-500">{{ $opportunity->company_name }}</p>
        @endif

        <p class="text-gray-600">
            Country: {{ $opportunity->country_name ?? 'No Country' }}
        </p>

        <p class="text-gray-600">
            Project Application: {{ $opportunity->project_application }}
        </p>

        @if($opportunity->estimated_amount)
        <p class="text-gray-600 text-sm">
            Estimated Amount: <span class="font-semibold text-gray-800">${{ number_format($opportunity->estimated_amount, 2) }}</span>
        </p>
        @endif

        <div class="mt-2">
            @php
                $statusColors = [
                    'Qualification'            => 'bg-blue-100 text-blue-700',
                    'Advance Closing'          => 'bg-purple-100 text-purple-700',
                    'Closed Won'               => 'bg-green-100 text-green-700',
                    'Closed Lost'              => 'bg-red-100 text-red-700',
                    'Closed Lost to Competition' => 'bg-orange-100 text-orange-700',
                ];
                $colorClass = $statusColors[$opportunity->status] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                {{ $opportunity->status }}
            </span>
        </div>

        {{-- Closed Won --}}
        @if($opportunity->status === 'Closed Won' && $opportunity->closed_won_percentage)
        <p class="text-green-600 font-semibold mt-1 text-sm">
            Closed Won %: {{ $opportunity->closed_won_percentage }}%
        </p>
        @endif

        {{-- Closed Lost --}}
        @if(in_array($opportunity->status, ['Closed Lost', 'Closed Lost to Competition']) && $opportunity->closed_lost_reason)
        <p class="text-red-600 font-semibold mt-1 text-sm">
            Reason: {{ $opportunity->closed_lost_reason }}
        </p>
        @endif

    </div>


    {{-- EXPANDABLE SECTION --}}
    <div x-show="open" x-transition class="px-6 pb-6">

        {{-- ACTION BUTTONS --}}
        <div class="flex gap-3 mb-4">
            <a href="{{ route('opportunities.edit', $opportunity->id) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                Edit
            </a>

            <form method="POST" action="{{ route('opportunities.destroy', $opportunity->id) }}"
                  onsubmit="return confirm('Delete this opportunity and all its products/activities?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                    Delete
                </button>
            </form>
        </div>


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
                            <th class="px-3 py-2 border text-left">Volume</th>
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

</div>

@endforeach

<!-- Pagination -->
<div class="mt-4">
{{ $opportunities->links() }}
</div>

</div>
</div>
<script>

function searchOpportunities(){

let input = document.getElementById("searchInput").value.toLowerCase();

let cards = document.querySelectorAll(".opportunity-card");

cards.forEach(function(card){

let text = card.innerText.toLowerCase();

if(text.includes(input)){

card.style.display = "block";

}else{

card.style.display = "none";

}

});

}

</script>
</x-app-layout>