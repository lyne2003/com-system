<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Edit Manufacturer
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-lg mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow rounded-lg p-6">

        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('manufacturers.update', $manufacturer->id) }}">
        @csrf

            {{-- Name --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $manufacturer->name) }}"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active --}}
            <div class="mb-6 flex items-center gap-2">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    value="1"
                    class="rounded border-gray-300"
                    {{ old('is_active', $manufacturer->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">
                    Update
                </button>
                <a href="{{ route('manufacturers.index') }}"
                    class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-6 py-2 rounded-lg shadow-md">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>
</div>

</x-app-layout>
