<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Company
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white p-6 shadow rounded-lg">

            <form method="POST" action="{{ route('companies.update', $company->id) }}">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-4">
                    <label class="block mb-1">Name</label>
                    <input type="text" name="name"
                           value="{{ old('name', $company->name) }}"
                           class="w-full border rounded px-3 py-2" required>
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block mb-1">Email</label>
                    <input type="email" name="email"
                           value="{{ old('email', $company->email) }}"
                           class="w-full border rounded px-3 py-2">
                </div>

                {{-- Website --}}
                <div class="mb-4">
                    <label class="block mb-1">Website</label>
                    <input type="text" name="website"
                           value="{{ old('website', $company->website) }}"
                           class="w-full border rounded px-3 py-2">
                </div>

                {{-- Region --}}
                <div class="mb-4">
                    <label class="block mb-1">Region</label>
<select name="region_id" id="regionSelect"
        class="w-full border rounded px-3 py-2">
    <option value="">Select Region</option>
    @foreach($regions as $region)
        <option value="{{ $region->id }}"
            {{ (string)$company->region_id === (string)$region->id ? 'selected' : '' }}>
            {{ $region->name }}
        </option>
    @endforeach
</select>
                </div>

                {{-- Country --}}
                <div class="mb-4">
                    <label class="block mb-1">Country</label>
<select name="country_id" id="countrySelect"
        class="w-full border rounded px-3 py-2">
    <option value="">Select Country</option>
</select>
                </div>

                {{-- Type --}}
                <div class="mb-4">
                    <label class="block mb-1">Type</label>
                    <select name="type_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                {{ $company->type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Industry --}}
                <div class="mb-4">
                    <label class="block mb-1">Industry</label>
                    <select name="industry_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Industry</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->id }}"
                                {{ $company->industry_id == $industry->id ? 'selected' : '' }}>
                                {{ $industry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tier --}}
                <div class="mb-4">
                    <label class="block mb-1">Tier</label>
                    <select name="tier_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Tier</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}"
                                {{ $company->tier_id == $tier->id ? 'selected' : '' }}>
                                {{ $tier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Assigned Sales --}}
                <div class="mb-4">
                    <label class="block mb-1">Assigned Sales</label>
                    <select name="assigned_sales_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Assigned Sales</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ $company->assigned_sales_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Assigned Engineer --}}
                <div class="mb-4">
                    <label class="block mb-1">Assigned Engineer</label>
                    <select name="assigned_engineer_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Assigned Engineer</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ $company->assigned_engineer_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <label class="block mb-1">Status</label>
                    <select name="status_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}"
                                {{ $company->status_id == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-between items-center mt-8 pt-6 border-t">
                    <a href="{{ route('companies.index') }}"
                       class="text-gray-600 hover:underline">
                        ← Back
                    </a>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Company
                    </button>
                </div>

            </form>

        </div>
    </div>

    {{-- Region → Country Dynamic Script --}}
<script>
    const regionCountries = @json($countries);
    const selectedRegion = "{{ $company->region_id }}";
    const selectedCountry = "{{ $company->country_id }}";

    function loadCountries(regionId, selected = null) {
        const countrySelect = document.getElementById('countrySelect');
        countrySelect.innerHTML = '<option value="">Select Country</option>';

        if (!regionId) return;

        const filtered = regionCountries.filter(item => item.region_id === regionId);

        filtered.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;

            if (selected && selected === item.id) {
                option.selected = true;
            }

            countrySelect.appendChild(option);
        });
    }

    document.getElementById('regionSelect').addEventListener('change', function () {
        loadCountries(this.value);
    });

    // 🔥 Load correct countries on page load
    if (selectedRegion) {
        loadCountries(selectedRegion, selectedCountry);
    }
</script>

</x-app-layout>