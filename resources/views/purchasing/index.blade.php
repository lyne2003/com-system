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
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Overall Code</th>
                    <th class="px-4 py-3 text-left">Order Code</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Part Number</th>
                    <th class="px-4 py-3 text-left">Mouser Part Number</th>
                    <th class="px-4 py-3 text-left">Mouser Price</th>
                    <th class="px-4 py-3 text-left">Stock</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                    {{ $row->overallcode ?? '—' }}
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800">
                    {{ $row->order_code ?? '—' }}
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '—' }}
                </td>
                <td class="px-4 py-3 font-mono font-bold text-gray-900">
                    {{ $row->partnumber }}
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-700">
                    {{ $row->mouser_part_number ?? '—' }}
                </td>
                <td class="px-4 py-3 font-mono font-semibold text-gray-800">
                    @if($row->mouser_price !== null)
                        ${{ number_format($row->mouser_price, 4) }}
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">
                    @if($row->mouser_stock !== null)
                        {{ number_format($row->mouser_stock) }}
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if(!$row->mouser_status)
                        <span class="text-gray-400 text-xs">Not sourced</span>
                    @elseif($row->mouser_status === 'found')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">✅ Found</span>
                    @elseif($row->mouser_status === 'no_stock')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">⚠️ No Stock</span>
                    @elseif($row->mouser_status === 'not_found')
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">❌ Not Found</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">{{ ucfirst($row->mouser_status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-gray-400">
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
