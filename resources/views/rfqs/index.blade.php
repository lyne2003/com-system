<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        RFQs
    </h2>
    <a href="{{ route('rfqs.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
        + Add RFQ
    </a>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('rfqs.index') }}" class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Reference, inquiry #, client..."
                    class="border rounded px-3 py-2 w-64 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Priority</label>
                <select name="priority" class="border rounded px-3 py-2 text-sm">
                    <option value="">All Priorities</option>
                    <option value="Low" @if(request('priority')=='Low') selected @endif>Low</option>
                    <option value="Medium" @if(request('priority')=='Medium') selected @endif>Medium</option>
                    <option value="High" @if(request('priority')=='High') selected @endif>High</option>
                    <option value="Urgent" @if(request('priority')=='Urgent') selected @endif>Urgent</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Client</label>
                <select name="client_id" class="border rounded px-3 py-2 text-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @if(request('client_id')==$client->id) selected @endif>
                        {{ $client->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                Filter
            </button>

            <a href="{{ route('rfqs.index') }}"
                class="px-4 py-2 bg-gray-400 text-white text-sm rounded hover:bg-gray-500 transition">
                Reset
            </a>

        </div>
    </form>

    {{-- RFQ CARDS --}}
    @forelse($rfqs as $rfq)

    <div x-data="{ open: false }" class="bg-white shadow rounded-lg mb-4">

        {{-- CLICKABLE HEADER --}}
        <div @click="open = !open" class="p-5 cursor-pointer hover:bg-gray-50 transition rounded-lg">
            <div class="flex justify-between items-start">

                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $rfq->reference }}
                        </h3>

                        {{-- Priority Badge --}}
                        @if($rfq->priority)
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            @if($rfq->priority == 'Urgent') bg-red-100 text-red-700
                            @elseif($rfq->priority == 'High') bg-orange-100 text-orange-700
                            @elseif($rfq->priority == 'Medium') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $rfq->priority }}
                        </span>
                        @endif

                        {{-- Item count badge --}}
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ isset($items[$rfq->id]) ? count($items[$rfq->id]) : 0 }} items
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                        @if($rfq->client_name)
                        <span>🏢 {{ $rfq->client_name }}</span>
                        @endif

                        @if($rfq->inquiry_n)
                        <span>📋 Inquiry #: {{ $rfq->inquiry_n }}</span>
                        @endif

                        @if($rfq->date)
                        <span>📅 {{ \Carbon\Carbon::parse($rfq->date)->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Actions + Expand arrow --}}
                <div class="flex items-center gap-3 mt-1">
                    <a href="{{ route('rfqs.edit', $rfq->id) }}"
                       @click.stop
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Edit
                    </a>

                    <form method="POST" action="{{ route('rfqs.destroy', $rfq->id) }}"
                          @click.stop
                          onsubmit="return confirm('Delete this RFQ and all its items?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                            Delete
                        </button>
                    </form>

                    <div class="text-gray-400">
                        <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

            </div>
        </div>

        {{-- EXPANDABLE SECTION --}}
        <div x-show="open" x-transition class="border-t border-gray-100 px-5 pb-5">

            {{-- Notes --}}
            @if($rfq->notes_to_purchasing || $rfq->notes_to_elias)
            <div class="grid grid-cols-2 gap-4 mt-4 mb-4">
                @if($rfq->notes_to_purchasing)
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Notes to Purchasing</p>
                    <p class="text-sm text-gray-700">{{ $rfq->notes_to_purchasing }}</p>
                </div>
                @endif
                @if($rfq->notes_to_elias)
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Notes to Elias</p>
                    <p class="text-sm text-gray-700">{{ $rfq->notes_to_elias }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Items Table --}}
            <h4 class="font-semibold text-gray-700 mb-3 mt-4">Items</h4>

            @if(isset($items[$rfq->id]) && count($items[$rfq->id]) > 0)

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 border text-left text-gray-600">#</th>
                            <th class="px-3 py-2 border text-left text-gray-600">Overall Code</th>
                            <th class="px-3 py-2 border text-left text-gray-600">Part Number</th>
                            <th class="px-3 py-2 border text-left text-gray-600">Qty</th>
                            <th class="px-3 py-2 border text-left text-gray-600">UOM</th>
                            <th class="px-3 py-2 border text-left text-gray-600">Target Price</th>
                            <th class="px-3 py-2 border text-left text-gray-600">Manufacturer</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items[$rfq->id] as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border text-gray-500">{{ $item->line_number }}</td>
                            <td class="px-3 py-2 border text-gray-600">{{ $item->overallcode }}</td>
                            <td class="px-3 py-2 border font-medium text-gray-800">{{ $item->partnumber }}</td>
                            <td class="px-3 py-2 border">{{ $item->qty }}</td>
                            <td class="px-3 py-2 border text-gray-600">{{ $item->uom }}</td>
                            <td class="px-3 py-2 border">
                                @if($item->target_price)
                                    ${{ number_format($item->target_price, 2) }}
                                @endif
                            </td>
                            <td class="px-3 py-2 border text-gray-600">{{ $item->manufacturer_name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @else
            <p class="text-gray-400 text-sm">No items added.</p>
            @endif

        </div>

    </div>

    @empty

    <div class="bg-white shadow rounded-lg p-8 text-center text-gray-500">
        No RFQs found. <a href="{{ route('rfqs.create') }}" class="text-blue-600 hover:underline">Create the first one</a>.
    </div>

    @endforelse

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $rfqs->appends(request()->query())->links() }}
    </div>

</div>
</div>

</x-app-layout>
