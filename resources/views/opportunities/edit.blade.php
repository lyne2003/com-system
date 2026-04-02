<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Edit Opportunity
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('opportunities.update', $opportunity->id) }}">
    @csrf

    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">Opportunity Information</h3>

        <div class="grid grid-cols-2 gap-6">

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Opportunity Name</label>
                <input type="text" name="opportunity_name"
                    value="{{ $opportunity->opportunity_name ?? '' }}"
                    class="w-full border rounded p-2" placeholder="e.g. Q2 2026 - ACME Corp - Industrial Drive">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                <select name="company_id" id="companySelect" class="w-full border rounded p-2"
                        onchange="filterContacts(this.value)">
                    <option value="">-- Select Client --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" @if($company->id == $opportunity->company_id) selected @endif>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <select name="contact_id" id="contactSelect" class="w-full border rounded p-2">
                    <option value="">-- Select Contact --</option>
                    {{-- Populated by JS on load --}}
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                <select name="manufacturer_id" class="w-full border rounded p-2">
                    <option value="">-- Select Manufacturer --</option>
                    @foreach($manufacturers as $mfr)
                    <option value="{{ $mfr->id }}" @if($mfr->id == ($opportunity->manufacturer_id ?? null)) selected @endif>
                        {{ $mfr->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <select name="country_id" class="w-full border rounded p-2">
                    <option value="">-- Select Country --</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}" @if($country->id == $opportunity->country_id) selected @endif>
                        {{ $country->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Application</label>
                <input type="text" name="project_application"
                    value="{{ $opportunity->project_application }}"
                    class="w-full border rounded p-2"
                    placeholder="e.g. Industrial Automation">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="statusSelect" class="w-full border rounded p-2"
                        onchange="toggleClosedFields()">
                    <option value="Qualification" @if($opportunity->status == 'Qualification') selected @endif>Qualification</option>
                    <option value="Advance Closing" @if($opportunity->status == 'Advance Closing') selected @endif>Advance Closing</option>
                    <option value="Closed Won" @if($opportunity->status == 'Closed Won') selected @endif>Closed Won</option>
                    <option value="Closed Lost" @if($opportunity->status == 'Closed Lost') selected @endif>Closed Lost</option>
                    <option value="Closed Lost to Competition" @if($opportunity->status == 'Closed Lost to Competition') selected @endif>Closed Lost to Competition</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Sales</label>
                <select name="assigned_sales_id" class="w-full border rounded p-2">
                    <option value="">-- Select --</option>
                    @foreach($sales as $user)
                    <option value="{{ $user->id }}" @if($user->id == $opportunity->assigned_sales_id) selected @endif>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Engineer</label>
                <select name="assigned_engineer_id" class="w-full border rounded p-2">
                    <option value="">-- Select --</option>
                    @foreach($engineers as $user)
                    <option value="{{ $user->id }}" @if($user->id == $opportunity->assigned_engineer_id) selected @endif>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Amount (USD)</label>
                <input type="number" name="estimated_amount"
                    value="{{ $opportunity->estimated_amount ?? '' }}"
                    step="0.01" min="0"
                    class="w-full border rounded p-2" placeholder="e.g. 50000">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded p-2"
                    placeholder="Additional notes...">{{ $opportunity->notes }}</textarea>
            </div>

        </div>

        {{-- Closed Won / Closed Lost conditional fields --}}
        <div id="closedWonBox" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closed Won %</label>
            <input type="number" name="closed_won_percentage"
                value="{{ $opportunity->closed_won_percentage }}"
                class="w-full border rounded p-2 max-w-xs"
                placeholder="e.g. 80" min="0" max="100">
        </div>

        <div id="closedLostBox" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closed Lost Reason</label>
            <select name="closed_lost_reason" class="w-full border rounded p-2 max-w-xs">
                <option value="">-- Select reason --</option>
                <option value="Price too high" @if($opportunity->closed_lost_reason == 'Price too high') selected @endif>Price too high</option>
                <option value="No stock" @if($opportunity->closed_lost_reason == 'No stock') selected @endif>No stock</option>
                <option value="Competitor" @if($opportunity->closed_lost_reason == 'Competitor') selected @endif>Competitor</option>
                <option value="Project canceled" @if($opportunity->closed_lost_reason == 'Project canceled') selected @endif>Project canceled</option>
                <option value="Client not responding" @if($opportunity->closed_lost_reason == 'Client not responding') selected @endif>Client not responding</option>
                <option value="Technical rejection" @if($opportunity->closed_lost_reason == 'Technical rejection') selected @endif>Technical rejection</option>
                <option value="Other" @if($opportunity->closed_lost_reason == 'Other') selected @endif>Other</option>
            </select>
        </div>

    </div>


    {{-- PRODUCTS --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">Products</h3>

        <div class="overflow-x-auto">
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Part Number</th>
                    <th class="border p-2 text-left">Qty</th>
                    <th class="border p-2 text-left">Volume</th>
                    <th class="border p-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody id="productsTable">
                @foreach($products as $index => $product)
                <tr class="product-row">
                    <td><input name="products[{{ $index }}][part_number]" value="{{ $product->part_number }}" class="w-full border p-1"></td>
                    <td><input name="products[{{ $index }}][quantity]" type="number" value="{{ $product->quantity }}" class="w-full border p-1"></td>
                    <td><input name="products[{{ $index }}][unit_price]" type="number" step="0.0001" value="{{ $product->unit_price }}" class="w-full border p-1"></td>
                    <td class="text-center">
                        <button type="button" onclick="removeProduct(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div class="mt-3">
            <button type="button" onclick="addProduct()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-sm text-sm">
                + Add Product
            </button>
        </div>

    </div>


    {{-- ACTIVITIES --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">Activities</h3>

        <div id="activitiesContainer" class="space-y-4">
            @foreach($activities as $index => $activity)
            {{-- Existing activity cards rendered server-side --}}
            <div class="activity-card border rounded-lg p-4 bg-gray-50 relative" id="activity-card-{{ $index }}">
                <button type="button" onclick="removeActivity(this)"
                    class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xs font-semibold">✕ Remove</button>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="activities[{{ $index }}][type]" class="w-full border rounded p-2 text-sm"
                                onchange="toggleActivityFields(this, {{ $index }})">
                            <option value="">-- Select Type --</option>
                            @foreach(['Task','Meeting','Call'] as $t)
                            <option value="{{ $t }}" @if(($activity->type ?? '') == $t) selected @endif>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Subject / Title</label>
                        <input type="text" name="activities[{{ $index }}][name]" value="{{ $activity->name }}"
                            class="w-full border rounded p-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                        <input type="date" name="activities[{{ $index }}][activity_date]" value="{{ $activity->activity_date }}"
                            class="w-full border rounded p-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="activities[{{ $index }}][status]" class="w-full border rounded p-2 text-sm">
                            <option value="Pending" @if($activity->status == 'Pending') selected @endif>Pending</option>
                            <option value="Completed" @if($activity->status == 'Completed') selected @endif>Completed</option>
                        </select>
                    </div>

                    {{-- Meeting-specific --}}
                    <div class="activity-field-meeting-{{ $index }} @if(($activity->type ?? '') != 'Meeting') hidden @endif">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Location</label>
                        <input type="text" name="activities[{{ $index }}][location]" value="{{ $activity->location ?? '' }}"
                            class="w-full border rounded p-2 text-sm" placeholder="e.g. Client office, Zoom">
                    </div>

                    <div class="activity-field-meeting-{{ $index }} @if(($activity->type ?? '') != 'Meeting') hidden @endif">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Attendees</label>
                        <input type="text" name="activities[{{ $index }}][attendees]" value="{{ $activity->attendees ?? '' }}"
                            class="w-full border rounded p-2 text-sm" placeholder="e.g. John, Sarah">
                    </div>

                    {{-- Call-specific --}}
                    <div class="activity-field-call-{{ $index }} @if(($activity->type ?? '') != 'Call') hidden @endif">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone Number</label>
                        <input type="text" name="activities[{{ $index }}][phone]" value="{{ $activity->phone ?? '' }}"
                            class="w-full border rounded p-2 text-sm" placeholder="e.g. +961 1 234567">
                    </div>

                    <div class="activity-field-call-{{ $index }} activity-field-meeting-{{ $index }} @if(!in_array($activity->type ?? '', ['Call','Meeting'])) hidden @endif">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Duration (minutes)</label>
                        <input type="number" name="activities[{{ $index }}][duration]" value="{{ $activity->duration ?? '' }}"
                            class="w-full border rounded p-2 text-sm" placeholder="e.g. 30">
                    </div>

                    {{-- Minutes / Notes --}}
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Minutes / Notes</label>
                        <textarea name="activities[{{ $index }}][minutes]" rows="3"
                            class="w-full border rounded p-2 text-sm"
                            placeholder="What happened? Key points, decisions, follow-ups...">{{ $activity->minutes ?? '' }}</textarea>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3">
            <button type="button" onclick="addActivity()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-sm text-sm">
                + Add Activity
            </button>
        </div>

    </div>


    {{-- SUBMIT --}}
    <div class="flex gap-3">
        <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-2 rounded-lg shadow-md">
            Update Opportunity
        </button>
        <a href="{{ route('opportunities.index') }}"
            class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-2 rounded-lg shadow-md">
            Cancel
        </a>
    </div>

    </form>

</div>
</div>

<script>

let productIndex = {{ count($products) }};

function addProduct() {
    const table = document.getElementById('productsTable');
    const row = `
    <tr class="product-row">
        <td><input name="products[${productIndex}][part_number]" class="w-full border p-1" placeholder="Required"></td>
        <td><input name="products[${productIndex}][quantity]" type="number" class="w-full border p-1"></td>
        <td><input name="products[${productIndex}][unit_price]" type="number" step="0.0001" class="w-full border p-1"></td>
        <td class="text-center">
            <button type="button" onclick="removeProduct(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
        </td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
    productIndex++;
}

function removeProduct(button) {
    button.closest('tr').remove();
}

let activityIndex = {{ count($activities) }};

function buildActivityCard(i, data) {
    data = data || {};
    const type     = data.type || '';
    const name     = data.name || '';
    const date     = data.activity_date || '';
    const status   = data.status || 'Pending';
    const location = data.location || '';
    const attendees= data.attendees || '';
    const phone    = data.phone || '';
    const duration = data.duration || '';
    const minutes  = data.minutes || '';

    const typeOptions = ['Task','Meeting','Call'].map(t =>
        `<option value="${t}" ${type===t?'selected':''}>${t}</option>`
    ).join('');

    const statusOptions = ['Pending','Completed'].map(s =>
        `<option value="${s}" ${status===s?'selected':''}>${s}</option>`
    ).join('');

    return `
    <div class="activity-card border rounded-lg p-4 bg-gray-50 relative" id="activity-card-${i}">
        <button type="button" onclick="removeActivity(this)"
            class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xs font-semibold">✕ Remove</button>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="activities[${i}][type]" class="w-full border rounded p-2 text-sm"
                        onchange="toggleActivityFields(this, ${i})">
                    <option value="">-- Select Type --</option>
                    ${typeOptions}
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Subject / Title</label>
                <input type="text" name="activities[${i}][name]" value="${name}"
                    class="w-full border rounded p-2 text-sm" placeholder="e.g. Follow-up call">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" name="activities[${i}][activity_date]" value="${date}"
                    class="w-full border rounded p-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="activities[${i}][status]" class="w-full border rounded p-2 text-sm">
                    ${statusOptions}
                </select>
            </div>
            <div class="activity-field-meeting-${i} hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Location</label>
                <input type="text" name="activities[${i}][location]" value="${location}"
                    class="w-full border rounded p-2 text-sm" placeholder="e.g. Client office, Zoom">
            </div>
            <div class="activity-field-meeting-${i} hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Attendees</label>
                <input type="text" name="activities[${i}][attendees]" value="${attendees}"
                    class="w-full border rounded p-2 text-sm" placeholder="e.g. John, Sarah">
            </div>
            <div class="activity-field-call-${i} hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone Number</label>
                <input type="text" name="activities[${i}][phone]" value="${phone}"
                    class="w-full border rounded p-2 text-sm" placeholder="e.g. +961 1 234567">
            </div>
            <div class="activity-field-call-${i} activity-field-meeting-${i} hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Duration (minutes)</label>
                <input type="number" name="activities[${i}][duration]" value="${duration}"
                    class="w-full border rounded p-2 text-sm" placeholder="e.g. 30">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Minutes / Notes</label>
                <textarea name="activities[${i}][minutes]" rows="3"
                    class="w-full border rounded p-2 text-sm"
                    placeholder="What happened? Key points, decisions, follow-ups...">${minutes}</textarea>
            </div>
        </div>
    </div>`;
}

function toggleActivityFields(select, i) {
    const type = select.value;
    document.querySelectorAll(`.activity-field-meeting-${i}, .activity-field-call-${i}`).forEach(el => {
        el.classList.add('hidden');
    });
    if (type === 'Meeting') {
        document.querySelectorAll(`.activity-field-meeting-${i}`).forEach(el => el.classList.remove('hidden'));
    } else if (type === 'Call') {
        document.querySelectorAll(`.activity-field-call-${i}`).forEach(el => el.classList.remove('hidden'));
    }
}

function addActivity(data) {
    const container = document.getElementById('activitiesContainer');
    container.insertAdjacentHTML('beforeend', buildActivityCard(activityIndex, data));
    const sel = document.querySelector(`#activity-card-${activityIndex} select[name="activities[${activityIndex}][type]"]`);
    if (sel && sel.value) toggleActivityFields(sel, activityIndex);
    activityIndex++;
}

function removeActivity(button) {
    button.closest('.activity-card').remove();
}

function toggleClosedFields() {
    const status = document.getElementById('statusSelect').value;
    const wonBox  = document.getElementById('closedWonBox');
    const lostBox = document.getElementById('closedLostBox');

    wonBox.classList.add('hidden');
    lostBox.classList.add('hidden');

    if (status === 'Closed Won')  wonBox.classList.remove('hidden');
    if (status === 'Closed Lost' || status === 'Closed Lost to Competition') lostBox.classList.remove('hidden');
}

// All contacts data from PHP (keyed by company_id)
const allContacts = @json($contacts->groupBy('company_id'));
const currentContactId = "{{ $opportunity->contact_id ?? '' }}";

function filterContacts(companyId, selectedId) {
    const select = document.getElementById('contactSelect');
    select.innerHTML = '<option value="">-- Select Contact --</option>';

    if (!companyId) return;

    const contacts = allContacts[companyId] || [];
    contacts.forEach(function(c) {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.firstname + ' ' + c.lastname;
        if (c.id == (selectedId || currentContactId)) opt.selected = true;
        select.appendChild(opt);
    });
}

// Run on page load to show correct fields for current status
document.addEventListener('DOMContentLoaded', function () {
    toggleClosedFields();

    // Pre-populate contacts for the current company
    const companySelect = document.getElementById('companySelect');
    if (companySelect && companySelect.value) {
        filterContacts(companySelect.value);
    }
});

</script>

</x-app-layout>
