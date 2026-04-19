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

<div class="py-6">
<div class="max-w-full mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-yellow-100">Notes to Purchasing</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Overall Code</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Order Code</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Part Number</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Mouser Part Number</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Type</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 1</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 2</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 3</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 4</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-blue-50">Supplier 5</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-yellow-50">Manufacturer</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S1</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S2</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S3</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-green-50">Brand S4</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcategory</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S1</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S2</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S3</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-purple-50">Subcat S4</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-orange-50">Line Volume ($)</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-orange-100 font-bold">Supplier Top 1</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-red-100 font-bold">Supplier Top 2</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-pink-100 font-bold">Supplier Top 3</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-rose-100 font-bold">Supplier Top 4</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap bg-teal-50">Online Pricing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            <tr class="hover:bg-gray-50">
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
                <td class="px-4 py-3 whitespace-nowrap">
                    @if($row->component_type === 'Active')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">⚡ Active</span>
                    @elseif($row->component_type === 'Passive')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">🔌 Passive</span>
                    @else
                        <span class="text-gray-400 text-xs">—</span>
                    @endif
                </td>
                @for($i = 0; $i < 5; $i++)
                <td class="px-4 py-3 text-xs font-semibold text-indigo-700 bg-blue-50 whitespace-nowrap">
                    {{ $row->recommended_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="px-4 py-3 text-xs font-semibold text-amber-700 bg-yellow-50 whitespace-nowrap">
                    {{ $row->rfq_manufacturer ?? $row->mouser_manufacturer ?? '—' }}
                </td>
                @for($i = 0; $i < 4; $i++)
                <td class="px-4 py-3 text-xs font-semibold text-green-700 bg-green-50 whitespace-nowrap">
                    {{ $row->brand_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="px-4 py-3 text-xs text-purple-600 bg-purple-50 whitespace-nowrap">
                    {{ $row->best_category ?? '—' }}
                </td>
                @for($i = 0; $i < 4; $i++)
                <td class="px-4 py-3 text-xs font-semibold text-purple-700 bg-purple-50 whitespace-nowrap">
                    {{ $row->subcategory_suppliers[$i] ?? '—' }}
                </td>
                @endfor
                <td class="px-4 py-3 text-xs text-orange-700 bg-orange-50 whitespace-nowrap text-right">
                    @if($row->volume !== null)
                        ${{ number_format($row->volume, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td class="px-4 py-3 text-xs font-bold text-orange-800 bg-orange-100 whitespace-nowrap">
                    {{ $row->supplier_top1 ?? '—' }}
                </td>
                <td class="px-4 py-3 text-xs font-bold text-red-800 bg-red-50 whitespace-nowrap">
                    {{ $row->supplier_top2 ?? '—' }}
                </td>
                <td class="px-4 py-3 text-xs font-bold text-pink-800 bg-pink-50 whitespace-nowrap">
                    {{ ($row->supplier_top3 ?? '') !== '' ? $row->supplier_top3 : '—' }}
                </td>
                <td class="px-4 py-3 text-xs font-bold text-rose-800 bg-rose-50 whitespace-nowrap">
                    {{ ($row->supplier_top4 ?? '') !== '' ? $row->supplier_top4 : '—' }}
                </td>
                <td class="px-4 py-3 text-xs font-semibold text-center bg-teal-50 whitespace-nowrap">
                    @if($row->volume !== null && $row->volume > 0 && $row->volume <= 300)
                        <span class="text-teal-700">Yes</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="25" class="px-4 py-10 text-center text-gray-400">
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

</x-app-layout>
