<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        🗂️ Supplier-Subcategories Matrix
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- Upload Card --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Upload Supplier-Subcategories Data</h3>
        <p class="text-sm text-gray-500 mb-4">
            Export the <strong>Supplier-subcategories</strong> sheet from Excel as <strong>Tab-separated (.txt)</strong> or CSV,
            then upload it here. The first row should be subcategory names; the first column should be supplier names.
            Uploading will <strong>replace all existing data</strong>.
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

        <form method="POST" action="{{ route('supplier_subcategories.upload') }}" enctype="multipart/form-data"
              class="flex items-center gap-4">
            @csrf
            <input type="file" name="csv_file" accept=".xlsx,.xls,.csv,.txt"
                   class="block text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                          file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700
                          hover:file:bg-purple-100 cursor-pointer">
            <button type="submit"
                    class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg">
                Upload & Replace
            </button>
        </form>
        <p class="mt-3 text-xs text-gray-400">
            Accepted: .xlsx, .xls, .csv, or .txt · Max size: 20 MB
        </p>
    </div>

    {{-- Matrix Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Current Matrix</h3>
            @if(!$isEmpty)
            <span class="text-sm text-gray-500">
                {{ count($suppliers) }} suppliers × {{ count($subcategories) }} subcategories
            </span>
            @endif
        </div>

        @if($isEmpty)
        <div class="px-6 py-12 text-center text-gray-400">
            No data yet. Upload the Supplier-subcategories sheet to populate the matrix.
        </div>
        @else
        <div class="overflow-x-auto">
        <table class="text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left font-semibold text-gray-700 border border-gray-200 sticky left-0 bg-gray-100 z-10 whitespace-nowrap min-w-[140px]">
                        Supplier \ Subcategory
                    </th>
                    @foreach($subcategories as $subcat)
                    <th class="px-2 py-2 font-semibold text-gray-600 border border-gray-200 whitespace-nowrap text-center"
                        style="writing-mode: vertical-rl; transform: rotate(180deg); min-width: 28px; max-width: 28px;">
                        {{ $subcat }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($suppliers as $supplier)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-1.5 font-semibold text-gray-800 border border-gray-200 sticky left-0 bg-white z-10 whitespace-nowrap">
                    {{ $supplier }}
                </td>
                @foreach($subcategories as $subcat)
                @php $count = $matrix[$supplier][$subcat] ?? 0; @endphp
                <td class="px-1 py-1.5 text-center border border-gray-200 font-mono
                    {{ $count > 0 ? 'bg-purple-50 text-purple-800 font-semibold' : 'text-gray-300' }}">
                    {{ $count > 0 ? $count : '' }}
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

</div>
</div>

</x-app-layout>
