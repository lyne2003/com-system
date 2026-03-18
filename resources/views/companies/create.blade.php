<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Create Account
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white p-6 shadow rounded-lg">

            <form method="POST" action="{{ route('companies.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1">Name</label>
                    <input type="text" name="name"
                           class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Email</label>
                    <input type="email" name="email"
                           class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Website</label>
                    <input type="text" name="website"
                           class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Region</label>
                    <select name="region_id" id="regionSelect"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Country</label>
                    <select name="country_id" id="countrySelect"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Country</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Type</label>
                    <select name="type_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Industry</label>
                    <select name="industry_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Industry</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->id }}">
                                {{ $industry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>                

                <div class="mb-4">
                    <label class="block mb-1">Tier</label>
                    <select name="tier_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Tier</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}">
                                {{ $tier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Assigned Sales</label>
                    <select name="assigned_sales_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Assigned Sales</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1">Assigned Engineer</label>
                    <select name="assigned_engineer_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Assigned Engineer</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>                

                <div class="mb-4">
                    <label class="block mb-1">Status</label>
                    <select name="status_id"
                            class="w-full border rounded px-3 py-2">
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-between items-center mt-8 pt-6 border-t">
                    <a href="{{ route('companies.index') }}"
                        class="text-gray-600 hover:underline">
                        ← Back
                    </a>

<div class="mt-8 pt-6 border-t text-right">
    <button type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded-md">
        Save
    </button>
</div>
                </div>
            </form>

        </div>
    </div>

<script>
const regionCountries = @json($countries);

document.getElementById('regionSelect').addEventListener('change', function () {
    const regionId = this.value;
    const countrySelect = document.getElementById('countrySelect');

    countrySelect.innerHTML = '<option value="">Select Country</option>';

    if (!regionId) return;

    const filtered = regionCountries.filter(item => item.region_id === regionId);

    filtered.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.name;
        countrySelect.appendChild(option);
    });
});
</script>
</x-app-layout>