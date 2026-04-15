<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        📦 SMO Suppliers (China)
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- Upload Card --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Upload Supplier Data (CSV)</h3>
        <p class="text-sm text-gray-500 mb-4">
            Export the <strong>Supplier-Type</strong> sheet from Excel as CSV, then upload it here.
            The file should have columns: <code class="bg-gray-100 px-1 rounded">Name, Active, Passive</code>
            (or <code class="bg-gray-100 px-1 rounded">ID, Name, Active, Passive</code> if exported with row numbers).
            Uploading will <strong>replace all existing supplier data</strong>.
        </p>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
            ❌ {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
            @foreach($errors->all() as $error)
                <div>❌ {{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('smo_suppliers.upload') }}" enctype="multipart/form-data"
              class="flex items-center gap-4">
            @csrf
            <input type="file" name="csv_file" accept=".csv,.txt"
                   class="block text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                          file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100 cursor-pointer">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                Upload & Replace
            </button>
        </form>

        <p class="mt-3 text-xs text-gray-400">
            Accepted formats: .csv or .txt · Max size: 2 MB
        </p>
    </div>

    {{-- Current Data Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Current Supplier Data</h3>
            <span class="text-sm text-gray-500">{{ $suppliers->count() }} suppliers</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">Supplier Name</th>
                    <th class="px-6 py-3 text-right">Active Count</th>
                    <th class="px-6 py-3 text-right">Passive Count</th>
                    <th class="px-6 py-3 text-right">% Active</th>
                    <th class="px-6 py-3 text-right">% Passive</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @php
                $totalActive  = $suppliers->sum('active_count');
                $totalPassive = $suppliers->sum('passive_count');
            @endphp
            @forelse($suppliers->sortByDesc('active_count') as $s)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-semibold text-gray-800">{{ $s->name }}</td>
                <td class="px-6 py-3 text-right font-mono text-gray-700">
                    {{ number_format($s->active_count) }}
                </td>
                <td class="px-6 py-3 text-right font-mono text-gray-700">
                    {{ number_format($s->passive_count) }}
                </td>
                <td class="px-6 py-3 text-right text-gray-500">
                    @if($totalActive > 0)
                        {{ number_format($s->active_count / $totalActive * 100, 1) }}%
                    @else
                        0%
                    @endif
                </td>
                <td class="px-6 py-3 text-right text-gray-500">
                    @if($totalPassive > 0)
                        {{ number_format($s->passive_count / $totalPassive * 100, 1) }}%
                    @else
                        0%
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                    No supplier data yet. Upload a CSV to get started.
                </td>
            </tr>
            @endforelse
            </tbody>
            @if($suppliers->count() > 0)
            <tfoot class="bg-gray-50 text-xs font-semibold text-gray-600 border-t">
                <tr>
                    <td class="px-6 py-3">Total</td>
                    <td class="px-6 py-3 text-right font-mono">{{ number_format($totalActive) }}</td>
                    <td class="px-6 py-3 text-right font-mono">{{ number_format($totalPassive) }}</td>
                    <td class="px-6 py-3 text-right">100%</td>
                    <td class="px-6 py-3 text-right">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
        </div>
    </div>

</div>
</div>

</x-app-layout>
