<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sourcing Results — {{ $rfq->reference }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            @if($rfq->client_name) 🏢 {{ $rfq->client_name }} &nbsp;|&nbsp; @endif
            📅 {{ $rfq->date ? \Carbon\Carbon::parse($rfq->date)->format('d M Y') : '—' }}
        </p>
    </div>
    <div class="flex gap-3">
        {{-- Re-run sourcing --}}
        <form method="POST" action="{{ route('rfqs.source.run', $rfq->id) }}"
              onsubmit="return confirm('Re-run sourcing for all items? This will overwrite existing results.')">
            @csrf
            <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow">
                🔄 Re-run Sourcing
            </button>
        </form>
        <a href="{{ route('rfqs.index') }}"
           class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-semibold rounded-lg shadow">
            ← Back to RFQs
        </a>
    </div>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    {{-- Messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('info'))
    <div class="mb-4 px-4 py-3 bg-blue-100 text-blue-800 rounded-lg">{{ session('info') }}</div>
    @endif

    {{-- ===== PROGRESS BAR (shown while processing) ===== --}}
    @php $sourcingStatus = $rfq->sourcing_status ?? 'idle'; @endphp

    @if($sourcingStatus === 'processing')
    <div id="progressSection" class="mb-6 bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">⏳ Sourcing in progress…</h3>
                <p class="text-sm text-gray-500 mt-1">Results are being fetched from Mouser, Digi-Key and TI in the background. This page updates automatically.</p>
            </div>
            <span id="progressPercent" class="text-2xl font-bold text-blue-600">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div id="progressBar" class="bg-blue-500 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-2" id="progressLabel">Checking…</p>
    </div>
    @endif

    @if($sourcingStatus === 'failed')
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg">
        ❌ Sourcing failed for one or more items. You can re-run sourcing using the button above.
    </div>
    @endif

    {{-- ===== ITEMS TABLE ===== --}}
    @if($items->isEmpty())
    <div class="bg-white shadow rounded-lg p-8 text-center text-gray-500">
        No items in this RFQ.
    </div>
    @else

    <div id="resultsContainer">
    @foreach($items as $item)
    @php
        $itemResults = $results[$item->id] ?? collect();
        $bySupplier  = $itemResults->keyBy('supplier');
        $suppliers   = ['mouser', 'digikey', 'ti'];
        $supplierLabels = ['mouser' => '🟠 Mouser', 'digikey' => '🔵 Digi-Key', 'ti' => '🔴 Texas Instruments'];
    @endphp

    <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">

        {{-- Item Header --}}
        <div class="bg-gray-50 border-b px-5 py-3 flex items-center gap-4">
            <span class="text-xs font-bold text-gray-400 uppercase">Line {{ $item->line_number }}</span>
            <span class="font-mono font-bold text-gray-800 text-base">{{ $item->partnumber }}</span>
            @if($item->qty)
            <span class="text-sm text-gray-500">Qty: <strong>{{ $item->qty }}</strong> {{ $item->uom }}</span>
            @endif
            @if($item->target_price)
            <span class="text-sm text-gray-500">Target: <strong>${{ number_format($item->target_price, 4) }}</strong></span>
            @endif
            @if($item->overallcode)
            <span class="font-mono text-xs bg-gray-200 px-2 py-0.5 rounded text-gray-600">{{ $item->overallcode }}</span>
            @endif
        </div>

        {{-- Supplier Results --}}
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-2 text-left w-40">Supplier</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Unit Price</th>
                    <th class="px-4 py-2 text-left">Stock</th>
                    <th class="px-4 py-2 text-left">Lead Time</th>
                    <th class="px-4 py-2 text-left">MOQ</th>
                    <th class="px-4 py-2 text-left">Package</th>
                    <th class="px-4 py-2 text-left">Manufacturer PN</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Datasheet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @foreach($suppliers as $supplierKey)
            @php $r = $bySupplier[$supplierKey] ?? null; @endphp
            <tr class="hover:bg-gray-50
                @if(!$r) bg-gray-50 opacity-60
                @elseif($r->status === 'found') bg-white
                @elseif($r->status === 'no_stock') bg-yellow-50
                @elseif($r->status === 'not_found') bg-red-50
                @else bg-orange-50
                @endif">

                <td class="px-4 py-3 font-semibold text-gray-700">
                    {{ $supplierLabels[$supplierKey] }}
                </td>

                <td class="px-4 py-3">
                    @if(!$r)
                        <span class="text-gray-400 text-xs">Pending…</span>
                    @elseif($r->status === 'found')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">✅ Found</span>
                    @elseif($r->status === 'no_stock')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">⚠️ No Stock</span>
                    @elseif($r->status === 'not_found')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">❌ Not Found</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">⚡ {{ ucfirst($r->status) }}</span>
                    @endif
                </td>

                <td class="px-4 py-3 font-mono font-bold text-gray-800">
                    @if($r && $r->unit_price !== null)
                        ${{ number_format($r->unit_price, 4) }}
                        @if($item->target_price && $r->unit_price <= $item->target_price)
                            <span class="ml-1 text-green-600 text-xs">✓ under target</span>
                        @elseif($item->target_price && $r->unit_price > $item->target_price)
                            <span class="ml-1 text-red-500 text-xs">↑ over target</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>

                <td class="px-4 py-3">
                    @if($r && $r->availability !== null)
                        {{ number_format($r->availability) }}
                        @if($r->stock_status)
                            <div class="text-xs text-gray-400">{{ $r->stock_status }}</div>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>

                <td class="px-4 py-3 text-gray-600">
                    {{ $r->lead_time ?? '—' }}
                </td>

                <td class="px-4 py-3 text-gray-600">
                    {{ $r->moq ?? '—' }}
                </td>

                <td class="px-4 py-3 text-gray-600 text-xs">
                    {{ $r->package_type ?? '—' }}
                    @if($r && $r->package_qty)
                        <div class="text-gray-400">Qty: {{ $r->package_qty }}</div>
                    @endif
                </td>

                <td class="px-4 py-3 font-mono text-xs text-gray-700">
                    {{ $r->manufacturer_pn ?? '—' }}
                    @if($r && $r->manufacturer)
                        <div class="text-gray-400">{{ $r->manufacturer }}</div>
                    @endif
                </td>

                <td class="px-4 py-3 text-xs text-gray-600 max-w-xs truncate">
                    {{ $r->description ?? '—' }}
                </td>

                <td class="px-4 py-3">
                    @if($r && $r->datasheet_url && $r->datasheet_url !== 'N/A')
                        <a href="{{ $r->datasheet_url }}" target="_blank"
                           class="text-blue-600 hover:underline text-xs">📄 PDF</a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>

            </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        {{-- Sourced at timestamp --}}
        @if($itemResults->isNotEmpty())
        <div class="px-5 py-2 text-xs text-gray-400 border-t">
            Last sourced: {{ \Carbon\Carbon::parse($itemResults->first()->sourced_at)->format('d M Y H:i') }}
        </div>
        @endif

    </div>
    @endforeach
    </div>

    @endif

</div>
</div>

@if(($rfq->sourcing_status ?? 'idle') === 'processing')
<script>
(function() {
    const rfqId   = '{{ $rfq->id }}';
    const statusUrl = '/rfqs/' + rfqId + '/sourcing-status';
    let pollInterval;

    function poll() {
        fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const pct   = data.percent || 0;
                const label = data.sourced + ' / ' + data.total + ' items sourced';

                document.getElementById('progressBar').style.width    = pct + '%';
                document.getElementById('progressPercent').textContent = pct + '%';
                document.getElementById('progressLabel').textContent   = label;

                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(pollInterval);
                    // Reload the page to show all results
                    window.location.reload();
                }
            })
            .catch(() => {}); // silently ignore network errors
    }

    // Poll every 4 seconds
    poll();
    pollInterval = setInterval(poll, 4000);
})();
</script>
@endif

</x-app-layout>
