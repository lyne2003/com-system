<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Opportunities</h2>
    <a href="{{ route('opportunities.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 rounded-lg font-semibold text-sm text-white hover:bg-blue-700 transition shadow">
        + New Opportunity
    </a>
</div>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- SEARCH + FILTER BAR --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3 items-center">
        <input type="text" id="searchInput" placeholder="Search opportunities..."
            class="flex-1 min-w-[200px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
            onkeyup="searchOpportunities()">

        <select id="statusFilter" onchange="searchOpportunities()"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <option value="">All Stages</option>
            <option value="Qualification">Qualification</option>
            <option value="Advance Closing">Advance Closing</option>
            <option value="Closed Won">Closed Won</option>
            <option value="Closed Lost">Closed Lost</option>
            <option value="Closed Lost to Competition">Closed Lost to Competition</option>
        </select>
    </div>

    {{-- OPPORTUNITY CARDS --}}
    @forelse($opportunities as $opportunity)
    @php
        $statusColors = [
            'Qualification'              => ['bg' => 'bg-blue-50',   'badge' => 'bg-blue-100 text-blue-700',   'border' => 'border-blue-200'],
            'Advance Closing'            => ['bg' => 'bg-purple-50', 'badge' => 'bg-purple-100 text-purple-700','border' => 'border-purple-200'],
            'Closed Won'                 => ['bg' => 'bg-green-50',  'badge' => 'bg-green-100 text-green-700', 'border' => 'border-green-200'],
            'Closed Lost'                => ['bg' => 'bg-red-50',    'badge' => 'bg-red-100 text-red-700',     'border' => 'border-red-200'],
            'Closed Lost to Competition' => ['bg' => 'bg-orange-50', 'badge' => 'bg-orange-100 text-orange-700','border'=> 'border-orange-200'],
        ];
        $sc = $statusColors[$opportunity->status] ?? ['bg' => 'bg-gray-50', 'badge' => 'bg-gray-100 text-gray-600', 'border' => 'border-gray-200'];
        $oppProducts  = $products[$opportunity->id]  ?? collect();
        $oppActivities = $activities[$opportunity->id] ?? collect();
    @endphp

    <div x-data="{ open: false }"
         class="opportunity-card bg-white rounded-xl shadow-sm border {{ $sc['border'] }} overflow-hidden transition-all"
         data-status="{{ $opportunity->status }}">

        {{-- CARD HEADER --}}
        <div @click="open = !open"
             class="cursor-pointer {{ $sc['bg'] }} px-6 py-5 flex items-start justify-between gap-4">

            <div class="flex-1 min-w-0">
                {{-- Title row --}}
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h3 class="text-base font-bold text-gray-900 truncate">
                        {{ $opportunity->opportunity_name ?? ($opportunity->company_name ?? 'Unnamed Opportunity') }}
                    </h3>
                    <span class="shrink-0 px-3 py-0.5 rounded-full text-xs font-semibold {{ $sc['badge'] }}">
                        {{ $opportunity->status }}
                    </span>
                </div>

                {{-- Meta row --}}
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500 mt-1">
                    @if($opportunity->company_name)
                    <span>{{ $opportunity->company_name }}</span>
                    @endif
                    @if($opportunity->country_name)
                    <span>{{ $opportunity->country_name }}</span>
                    @endif
                    @if($opportunity->project_application)
                    <span>{{ $opportunity->project_application }}</span>
                    @endif
                    @if($opportunity->estimated_amount)
                    <span class="font-semibold text-gray-700">${{ number_format($opportunity->estimated_amount, 0) }}</span>
                    @endif
                </div>

                {{-- Closed details --}}
                @if($opportunity->status === 'Closed Won' && $opportunity->closed_won_percentage)
                <p class="text-xs text-green-600 font-semibold mt-1">Won at {{ $opportunity->closed_won_percentage }}%</p>
                @endif
                @if(in_array($opportunity->status, ['Closed Lost','Closed Lost to Competition']) && $opportunity->closed_lost_reason)
                <p class="text-xs text-red-500 font-semibold mt-1">{{ $opportunity->closed_lost_reason }}</p>
                @endif
            </div>

            {{-- Right side: stats + chevron --}}
            <div class="flex items-center gap-4 shrink-0">
                <div class="hidden sm:flex gap-3 text-xs text-gray-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ count($oppProducts) }} products
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ count($oppActivities) }} activities
                    </span>
                </div>
                <svg x-bind:class="open ? 'rotate-180' : ''"
                     class="w-5 h-5 text-gray-400 transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- EXPANDED BODY --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-gray-100">

            {{-- Action bar --}}
            <div class="flex items-center gap-3 px-6 py-3 bg-gray-50 border-b border-gray-100">
                <a href="{{ route('opportunities.edit', $opportunity->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                    Edit
                </a>
                <form method="POST" action="{{ route('opportunities.destroy', $opportunity->id) }}"
                      onsubmit="return confirm('Delete this opportunity and all its data?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                        Delete
                    </button>
                </form>
                @if($opportunity->notes)
                <p class="text-xs text-gray-400 italic ml-2 truncate max-w-md">{{ $opportunity->notes }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

                {{-- PRODUCTS --}}
                <div class="p-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Products</h4>

                    @if(count($oppProducts) > 0)
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-2 text-left">Part Number</th>
                                    <th class="px-4 py-2 text-right">Qty</th>
                                    <th class="px-4 py-2 text-right">Volume</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($oppProducts as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ $product->part_number }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($product->quantity) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $product->unit_price ? '$'.number_format($product->unit_price, 2) : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">No products added.</p>
                    @endif
                </div>

                {{-- ACTIVITIES --}}
                <div class="p-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Activities</h4>

                    @if(count($oppActivities) > 0)
                    <div class="space-y-3">
                        @foreach($oppActivities as $activity)
                        @php
                            $typeColors = [
                                'Task'    => 'bg-blue-50 border-blue-100',
                                'Meeting' => 'bg-purple-50 border-purple-100',
                                'Call'    => 'bg-teal-50 border-teal-100',
                            ];
                            $tc2 = $typeColors[$activity->type ?? ''] ?? 'bg-gray-50 border-gray-100';
                        @endphp
                        <div class="rounded-lg border {{ $tc2 }} p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        @if($activity->type)
                                        <span class="text-xs font-medium text-gray-400 mr-1">[{{ $activity->type }}]</span>
                                        @endif
                                        {{ $activity->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $activity->activity_date ? \Carbon\Carbon::parse($activity->activity_date)->format('d M Y') : '' }}
                                        @if($activity->location) · {{ $activity->location }} @endif
                                        @if($activity->duration) · {{ $activity->duration }} min @endif
                                    </p>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 rounded text-xs font-semibold
                                    @if($activity->status == 'Completed') bg-green-100 text-green-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ $activity->status }}
                                </span>
                            </div>
                            @if($activity->minutes)
                            <p class="mt-2 text-xs text-gray-500 bg-white rounded p-2 border border-gray-100 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($activity->minutes, 120) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">No activities recorded.</p>
                    @endif
                </div>

            </div>
        </div>

    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <p class="text-gray-400 text-lg">No opportunities found.</p>
        <a href="{{ route('opportunities.create') }}" class="mt-4 inline-block text-blue-600 hover:underline text-sm">+ Create your first opportunity</a>
    </div>
    @endforelse

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $opportunities->links() }}
    </div>

</div>
</div>

<script>
function searchOpportunities() {
    const input  = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value.toLowerCase();
    document.querySelectorAll('.opportunity-card').forEach(card => {
        const text       = card.innerText.toLowerCase();
        const cardStatus = (card.dataset.status || '').toLowerCase();
        const matchText  = !input  || text.includes(input);
        const matchStatus= !status || cardStatus === status;
        card.style.display = (matchText && matchStatus) ? '' : 'none';
    });
}
</script>

</x-app-layout>
