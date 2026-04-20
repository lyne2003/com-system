<x-app-layout>

<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🛒 Purchasing
        </h2>
        <form method="GET" action="{{ route('purchasing.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search part number, order code…"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 w-72">
            <button type="submit"
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('purchasing.index') }}"
               class="px-4 py-1.5 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg">
                Clear
            </a>
            @endif
        </form>
    </div>
</x-slot>

@php
$supplierOptions = array_unique([
    'Allcere','Ariat','ATP','B1B','Crassus','Hayma','LiangXin','IcKey',
    'Kehuite','Maxtronic','Perceptive','Jeking','SMYG','USIE','Wynn',
    'XKHG-IC','Hongfa','YND','ASCO','Anchor','Chipower Electronics',
    'Asai Kosan','X.MU','Shainor','SRH Electronics','Eastech','Oneyac',
    'Yuguang','Drsic','Coral','Future','Mouser','Digikey','Shenzhen',
    'DGT Industrial Limited','THJ','Briocean','Win Source','Cytech',
    'Microchip Direct','Avnet (HK)','Macnica (USA)',
    'Future // RFMW','Future // WT','Future // TTI','WPI','WT','Superco',
    'LinkIC','CJJ HK TECHNOLOGY LIMITED','Brightmile Limited',
    'YSX Tech Co., Ltd.','FANCO ELECTRONICS',
    'Vadas International Co., Ltd.','Flyking Technology Co .,Ltd.',
    'R&A Electronics Co., Ltd.',
    'Shenzhen WeiTaiXu Capacitors Co.,Ltd','Elias','Online Pricing',
    'None','Ordex','Omron','Micron','On Semi','Sourceability',
]);
@endphp

<style>
/* Hidden columns */
.col-details { display: none; }
.col-details-visible { display: table-cell; }
/* Processed row */
tr.row-processed td { background-color: #f0fdf4 !important; }
tr.row-processed td select { opacity: 0.6; pointer-events: none; }
</style>

<div class="py-6">
<div class="max-w-full mx-auto sm:px-6 lg:px-8">

    {{-- Toggle button --}}
    <div class="mb-3 flex items-center gap-3">
        <button id="toggleDetails"
                onclick="toggleDetailCols()"
                class="px-4 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg flex items-center gap-2">
            <span id="toggleIcon">▶</span>
            <span id="toggleLabel">Show Details</span>
        </button>
        <span class="text-xs text-gray-400">Type, Suppliers, Manufacturer, Brand, Subcategory, Volume, Top Suppliers, Online Pricing</span>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm" id="purchasingTable">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-yellow-100">Notes to Purchasing</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Overall Code</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Order Code</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Part Number</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Mouser Part Number</th>
                    {{-- Collapsible columns --}}
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap">Type</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 1</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 2</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 3</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 4</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 5</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-yellow-50">Manufacturer</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S1</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S2</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S3</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S4</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcategory</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S1</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S2</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S3</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S4</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-orange-50">Line Volume ($)</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-orange-100 font-bold">Supplier Top 1</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-red-100 font-bold">Supplier Top 2</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-pink-100 font-bold">Supplier Top 3</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-rose-100 font-bold">Supplier Top 4</th>
                    <th class="col-details px-4 py-3 text-left whitespace-nowrap bg-teal-50">Online Pricing</th>
                    {{-- Always visible --}}
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-indigo-100 font-bold">Assigned Supplier</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-violet-100 font-bold">Assigned Supplier 2</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-fuchsia-100 font-bold">Assigned Supplier 3</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-green-100 font-bold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            @php
                $onlinePricing = ($row->volume !== null && $row->volume > 0 && $row->volume <= 300)
                    || str_contains(strtolower($row->notes_to_purchasing ?? ''), 'budgetary');

                // Use saved values if available, otherwise fall back to computed defaults
                $assignedDefault  = $row->saved_supplier1 ?? ($onlinePricing ? 'Mouser'  : ($row->supplier_top1 ?? ''));
                $assigned2Default = $row->saved_supplier2 ?? ($onlinePricing ? 'Digikey' : ($row->supplier_top2 ?? ''));
                $assigned3Default = $row->saved_supplier3 ?? ($onlinePricing ? '' : ($row->supplier_top3 ?? ''));
                $isProcessed      = (bool)($row->is_processed ?? false);
            @endphp
            <tr class="hover:bg-gray-50 {{ $isProcessed ? 'row-processed' : '' }}"
                id="row-{{ $row->item_id }}"
                data-item-id="{{ $row->item_id }}"
                data-save-url="{{ route('purchasing.save', $row->item_id) }}">
                <td class="px-4 py-3 text-xs text-yellow-800 bg-yellow-50 max-w-xs">
                    {{ $row->notes_to_purchasing ?? '' }}
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">
                    {{ $row->overallcode ?? '—' }}
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800 whitespace-nowrap">
                    @if($row->date && $row->order_code)
                        {{ \Carbon\Carbon::parse($row->date)->format('Ymd') }}-{{ $row->order_code }}
                    @else
                        {{ $row->order_code ?? '—' }}
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '—' }}
                </td>
                <td class="px-4 py-3 font-mono font-bold text-gray-900 whitespace-nowrap">
                    {{ $row->partnumber }}
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-700 whitespace-nowrap">
                    {{ $row->mouser_part_number ?? '—' }}
                </td>
                {{-- Collapsible cells --}}
                <td class="col-details px-4 py-3 whitespace-nowrap">
                    @if($row->component_type === 'Active')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">⚡ Active</span>
                    @elseif($row->component_type === 'Passive')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">🔌 Passive</span>
                    @else
                        <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                @for($i = 0; $i < 5; $i++)
                <td class="col-details px-4 py-3 text-xs font-semibold text-indigo-700 bg-blue-50 whitespace-nowrap">
                    {{ $row->recommended_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="col-details px-4 py-3 text-xs font-semibold text-amber-700 bg-yellow-50 whitespace-nowrap">
                    {{ $row->rfq_manufacturer ?? $row->mouser_manufacturer ?? '—' }}
                </td>
                @for($i = 0; $i < 4; $i++)
                <td class="col-details px-4 py-3 text-xs font-semibold text-green-700 bg-green-50 whitespace-nowrap">
                    {{ $row->brand_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="col-details px-4 py-3 text-xs text-purple-600 bg-purple-50 whitespace-nowrap">
                    {{ $row->best_category ?? '—' }}
                </td>
                @for($i = 0; $i < 4; $i++)
                <td class="col-details px-4 py-3 text-xs font-semibold text-purple-700 bg-purple-50 whitespace-nowrap">
                    {{ $row->subcategory_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="col-details px-4 py-3 text-xs text-orange-700 bg-orange-50 whitespace-nowrap text-right">
                    @if($row->volume !== null)
                        ${{ number_format($row->volume, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td class="col-details px-4 py-3 text-xs font-bold text-orange-800 bg-orange-100 whitespace-nowrap">
                    {{ $row->supplier_top1 ?? '—' }}
                </td>
                <td class="col-details px-4 py-3 text-xs font-bold text-red-800 bg-red-50 whitespace-nowrap">
                    {{ $row->supplier_top2 ?? '—' }}
                </td>
                <td class="col-details px-4 py-3 text-xs font-bold text-pink-800 bg-pink-50 whitespace-nowrap">
                    {{ ($row->supplier_top3 ?? '') !== '' ? $row->supplier_top3 : '—' }}
                </td>
                <td class="col-details px-4 py-3 text-xs font-bold text-rose-800 bg-rose-50 whitespace-nowrap">
                    {{ ($row->supplier_top4 ?? '') !== '' ? $row->supplier_top4 : '—' }}
                </td>
                <td class="col-details px-4 py-3 text-xs font-semibold text-center bg-teal-50 whitespace-nowrap">
                    @if($onlinePricing)
                        <span class="text-teal-700">Yes</span>
                    @endif
                </td>
                {{-- Assigned Supplier 1 --}}
                <td class="px-4 py-3 bg-indigo-50 whitespace-nowrap">
                    <select class="sel-s1 text-xs border border-indigo-300 rounded px-2 py-1 bg-white text-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 w-44">
                        <option value="">— Select —</option>
                        @foreach($supplierOptions as $opt)
                            <option value="{{ $opt }}" @selected($opt === $assignedDefault)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </td>
                {{-- Assigned Supplier 2 --}}
                <td class="px-4 py-3 bg-violet-50 whitespace-nowrap">
                    <select class="sel-s2 text-xs border border-violet-300 rounded px-2 py-1 bg-white text-violet-800 focus:outline-none focus:ring-2 focus:ring-violet-400 w-44">
                        <option value="">— Select —</option>
                        @foreach($supplierOptions as $opt)
                            <option value="{{ $opt }}" @selected($opt === $assigned2Default)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </td>
                {{-- Assigned Supplier 3 --}}
                <td class="px-4 py-3 bg-fuchsia-50 whitespace-nowrap">
                    <select class="sel-s3 text-xs border border-fuchsia-300 rounded px-2 py-1 bg-white text-fuchsia-800 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 w-44">
                        <option value="">— Select —</option>
                        @foreach($supplierOptions as $opt)
                            <option value="{{ $opt }}" @selected($opt === $assigned3Default)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </td>
                {{-- Processed button --}}
                <td class="px-4 py-3 bg-green-50 whitespace-nowrap text-center">
                    @if($isProcessed)
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-green-700 font-semibold text-xs">✅ Processed</span>
                            @if($row->processed_at)
                                <span class="text-gray-400 text-xs">{{ \Carbon\Carbon::parse($row->processed_at)->format('d M Y') }}</span>
                            @endif
                            <button onclick="markRow(this, false)"
                                    class="text-xs text-gray-400 hover:text-red-500 underline mt-0.5">
                                Undo
                            </button>
                        </div>
                    @else
                        <button onclick="markRow(this, true)"
                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg whitespace-nowrap">
                            ✔ Mark Processed
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="32" class="px-4 py-10 text-center text-gray-400">
                    No items found.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        @if($rows->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $rows->links() }}
        </div>
        @endif
    </div>

</div>
</div>

<script>
let detailsVisible = false;

function toggleDetailCols() {
    detailsVisible = !detailsVisible;
    const cells = document.querySelectorAll('.col-details');
    cells.forEach(cell => {
        cell.style.display = detailsVisible ? 'table-cell' : 'none';
    });
    document.getElementById('toggleIcon').textContent  = detailsVisible ? '▼' : '▶';
    document.getElementById('toggleLabel').textContent = detailsVisible ? 'Hide Details' : 'Show Details';
}

function markRow(btn, isProcessed) {
    const tr = btn.closest('tr');
    const itemId  = tr.dataset.itemId;
    const saveUrl = tr.dataset.saveUrl;

    const s1 = tr.querySelector('.sel-s1')?.value ?? '';
    const s2 = tr.querySelector('.sel-s2')?.value ?? '';
    const s3 = tr.querySelector('.sel-s3')?.value ?? '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    btn.disabled = true;
    btn.textContent = '…';

    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            assigned_supplier:  s1,
            assigned_supplier2: s2,
            assigned_supplier3: s3,
            is_processed: isProcessed,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const statusCell = btn.closest('td');
            if (isProcessed) {
                tr.classList.add('row-processed');
                const today = new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                statusCell.innerHTML = `
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-green-700 font-semibold text-xs">✅ Processed</span>
                        <span class="text-gray-400 text-xs">${today}</span>
                        <button onclick="markRow(this, false)" class="text-xs text-gray-400 hover:text-red-500 underline mt-0.5">Undo</button>
                    </div>`;
            } else {
                tr.classList.remove('row-processed');
                tr.querySelectorAll('select').forEach(s => s.style.pointerEvents = '');
                statusCell.innerHTML = `<button onclick="markRow(this, true)" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg whitespace-nowrap">✔ Mark Processed</button>`;
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = isProcessed ? '✔ Mark Processed' : 'Undo';
        alert('Failed to save. Please try again.');
    });
}
</script>

</x-app-layout>
