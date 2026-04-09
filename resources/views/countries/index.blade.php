<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Countries
    </h2>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')"
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
        + Add Country
    </button>
</div>
</x-slot>

{{-- ADD COUNTRY MODAL --}}
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Add Country</h3>

        <form method="POST" action="{{ route('countries.store') }}">
        @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('name') border-red-500 @enderror"
                    placeholder="e.g. Lebanon"
                    autofocus>
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button"
                    onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('countries.index') }}" class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name..."
                    class="border rounded px-3 py-2 w-64 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="is_active" class="border rounded px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="1" @if(request('is_active')==='1') selected @endif>Active</option>
                    <option value="0" @if(request('is_active')==='0') selected @endif>Inactive</option>
                </select>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                Filter
            </button>

            <a href="{{ route('countries.index') }}"
                class="px-4 py-2 bg-gray-400 text-white text-sm rounded hover:bg-gray-500 transition">
                Reset
            </a>

        </div>
    </form>

    {{-- TABLE --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">

                @forelse($countries as $country)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $country->name }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($country->is_active)
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('countries.edit', $country->id) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>

                            <form method="POST" action="{{ route('countries.destroy', $country->id) }}"
                                  onsubmit="return confirm('Delete this country?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                        No countries found.
                        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                            class="text-blue-600 hover:underline">Add the first one</button>.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $countries->appends(request()->query())->links() }}
    </div>

</div>
</div>

@if($errors->has('name'))
<script>
    document.getElementById('addModal').classList.remove('hidden');
</script>
@endif

<script>
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});
</script>

</x-app-layout>
