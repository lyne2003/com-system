<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inquiry Number Rules</h2>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- SUCCESS / ERROR --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- INFO BOX --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
        <strong>How it works:</strong> Each rule defines a group name, a starting number, and a list of countries.
        When an RFQ is created, the client's country is matched against these rules to auto-assign the inquiry number.
        The counter resets every Monday back to <em>base number + 1</em>.
    </div>

    {{-- ADD NEW RULE FORM --}}
    <div x-data="{ open: false }">
        <button @click="open = !open"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow">
            + Add New Rule
        </button>

        <div x-show="open" x-transition class="mt-4 bg-white shadow rounded-lg p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">New Rule</h3>
            <form method="POST" action="{{ route('inquiry_rules.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                        <input type="text" name="group_name" value="{{ old('group_name') }}"
                            class="w-full border rounded p-2 text-sm @error('group_name') border-red-500 @enderror"
                            placeholder="e.g. Africa, GCC, Europe">
                        @error('group_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Starting Number (Base) <span class="text-red-500">*</span></label>
                        <input type="number" name="base_number" value="{{ old('base_number') }}"
                            class="w-full border rounded p-2 text-sm @error('base_number') border-red-500 @enderror"
                            placeholder="e.g. 10000">
                        <p class="text-xs text-gray-400 mt-1">First inquiry of the week = base + 1</p>
                        @error('base_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Countries <span class="text-gray-400 font-normal">(one per line, must match country name exactly)</span></label>
                    <textarea name="countries" rows="5"
                        class="w-full border rounded p-2 text-sm font-mono"
                        placeholder="Egypt&#10;Tunisia&#10;Algeria">{{ old('countries') }}</textarea>
                </div>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                    Save Rule
                </button>
            </form>
        </div>
    </div>

    {{-- EXISTING RULES --}}
    @forelse($rules as $rule)
    @php $ruleCountries = $countries[$rule->id] ?? collect(); @endphp

    <div x-data="{ editing: false }" class="bg-white shadow rounded-lg overflow-hidden">

        {{-- Rule Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800">{{ $rule->group_name }}</h3>
                    <p class="text-sm text-gray-500">
                        Starts from <strong>{{ number_format($rule->base_number + 1) }}</strong> each Monday
                        &nbsp;·&nbsp;
                        {{ $ruleCountries->count() }} {{ Str::plural('country', $ruleCountries->count()) }}
                        &nbsp;·&nbsp;
                        @if($rule->is_active)
                            <span class="text-green-600 font-semibold">Active</span>
                        @else
                            <span class="text-red-500 font-semibold">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="editing = !editing"
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg">
                    Edit
                </button>
                <form method="POST" action="{{ route('inquiry_rules.destroy', $rule->id) }}"
                      onsubmit="return confirm('Delete this rule?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- Countries list (view mode) --}}
        <div x-show="!editing" class="px-6 py-4">
            @if($ruleCountries->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($ruleCountries as $c)
                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">{{ $c->country_name }}</span>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 italic">No countries added yet.</p>
            @endif
        </div>

        {{-- Edit form --}}
        <div x-show="editing" x-transition class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('inquiry_rules.update', $rule->id) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                        <input type="text" name="group_name" value="{{ $rule->group_name }}"
                            class="w-full border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Starting Number (Base)</label>
                        <input type="number" name="base_number" value="{{ $rule->base_number }}"
                            class="w-full border rounded p-2 text-sm">
                        <p class="text-xs text-gray-400 mt-1">First inquiry of the week = base + 1</p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Countries (one per line)</label>
                    <textarea name="countries" rows="6"
                        class="w-full border rounded p-2 text-sm font-mono">{{ $ruleCountries->pluck('country_name')->implode("\n") }}</textarea>
                </div>
                <div class="flex items-center gap-4 mb-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" {{ $rule->is_active ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        Active
                    </label>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        Update Rule
                    </button>
                    <button type="button" @click="editing = false"
                        class="px-6 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-semibold rounded-lg">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

    </div>
    @empty
    <div class="bg-white shadow rounded-lg p-10 text-center text-gray-400">
        No inquiry rules defined yet. Add your first rule above.
    </div>
    @endforelse

</div>
</div>

</x-app-layout>
