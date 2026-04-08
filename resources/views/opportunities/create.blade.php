<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Add Opportunity
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

    <form method="POST" action="{{ route('opportunities.store') }}">
    @csrf

    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">Opportunity Information</h3>

        <div class="grid grid-cols-2 gap-6">

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Opportunity Name</label>
                <input type="text" name="opportunity_name" value="{{ old('opportunity_name') }}"
                    class="w-full border rounded p-2" placeholder="e.g. Q2 2026 - ACME Corp - Industrial Drive">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                <div class="flex gap-2">
                    <select name="company_id" id="companySelect" class="flex-1 border rounded p-2"
                            onchange="filterContacts(this.value)">
                        <option value="">-- Select Client --</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" @if(old('company_id') == $company->id) selected @endif>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openAddClientModal()"
                        class="shrink-0 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded border border-gray-300 transition"
                        title="Add new client">
                        + New
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <div class="flex gap-2">
                    <select name="contact_id" id="contactSelect" class="flex-1 border rounded p-2">
                        <option value="">-- Select Client first --</option>
                    </select>
                    <button type="button" onclick="openAddContactModal()"
                        class="shrink-0 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded border border-gray-300 transition"
                        title="Add new contact">
                        + New
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                <select name="manufacturer_id" class="w-full border rounded p-2">
                    <option value="">-- Select Manufacturer --</option>
                    @foreach($manufacturers as $mfr)
                    <option value="{{ $mfr->id }}" @if(old('manufacturer_id') == $mfr->id) selected @endif>
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
                    <option value="{{ $country->id }}" @if(old('country_id') == $country->id) selected @endif>
                        {{ $country->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Application</label>
                <input type="text" name="project_application" value="{{ old('project_application') }}"
                    class="w-full border rounded p-2" placeholder="e.g. Industrial Automation">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="statusSelect" class="w-full border rounded p-2"
                        onchange="toggleClosedFields()">
                    <option value="Qualification" @if(old('status','Qualification')=='Qualification') selected @endif>Qualification</option>
                    <option value="Advance Closing" @if(old('status')=='Advance Closing') selected @endif>Advance Closing</option>
                    <option value="Closed Won" @if(old('status')=='Closed Won') selected @endif>Closed Won</option>
                    <option value="Closed Lost" @if(old('status')=='Closed Lost') selected @endif>Closed Lost</option>
                    <option value="Closed Lost to Competition" @if(old('status')=='Closed Lost to Competition') selected @endif>Closed Lost to Competition</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Sales</label>
                <select name="assigned_sales_id" class="w-full border rounded p-2">
                    <option value="">-- Select --</option>
                    @foreach($sales as $user)
                    <option value="{{ $user->id }}" @if(old('assigned_sales_id') == $user->id) selected @endif>
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
                    <option value="{{ $user->id }}" @if(old('assigned_engineer_id') == $user->id) selected @endif>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Amount (USD)</label>
                <input type="number" name="estimated_amount" value="{{ old('estimated_amount') }}"
                    step="0.01" min="0"
                    class="w-full border rounded p-2" placeholder="e.g. 50000">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded p-2"
                    placeholder="Additional notes...">{{ old('notes') }}</textarea>
            </div>

        </div>

        {{-- Closed Won / Closed Lost conditional fields --}}
        <div id="closedWonBox" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closed Won %</label>
            <input type="number" name="closed_won_percentage" value="{{ old('closed_won_percentage') }}"
                class="w-full border rounded p-2 max-w-xs" placeholder="e.g. 80" min="0" max="100">
        </div>

        <div id="closedLostBox" class="mt-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Closed Lost Reason</label>
            <select name="closed_lost_reason" class="w-full border rounded p-2 max-w-xs">
                <option value="">-- Select reason --</option>
                <option value="Price too high" @if(old('closed_lost_reason')=='Price too high') selected @endif>Price too high</option>
                <option value="No stock" @if(old('closed_lost_reason')=='No stock') selected @endif>No stock</option>
                <option value="Competitor" @if(old('closed_lost_reason')=='Competitor') selected @endif>Competitor</option>
                <option value="Project canceled" @if(old('closed_lost_reason')=='Project canceled') selected @endif>Project canceled</option>
                <option value="Client not responding" @if(old('closed_lost_reason')=='Client not responding') selected @endif>Client not responding</option>
                <option value="Technical rejection" @if(old('closed_lost_reason')=='Technical rejection') selected @endif>Technical rejection</option>
                <option value="Other" @if(old('closed_lost_reason')=='Other') selected @endif>Other</option>
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
                <tr class="product-row">
                    <td><input name="products[0][part_number]" class="w-full border p-1" placeholder="Required"></td>
                    <td><input name="products[0][quantity]" type="number" class="w-full border p-1"></td>
                    <td><input name="products[0][unit_price]" type="number" step="0.0001" class="w-full border p-1"></td>
                    <td class="text-center">
                        <button type="button" onclick="removeProduct(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                    </td>
                </tr>
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
            <!-- Activity rows injected here -->
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
            Save Opportunity
        </button>
        <a href="{{ route('opportunities.index') }}"
            class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-2 rounded-lg shadow-md">
            Cancel
        </a>
    </div>

    </form>

</div>
</div>

{{-- ADD CLIENT MODAL --}}
<div id="addClientModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Add New Client</h3>
            <button type="button" onclick="closeAddClientModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </div>

        <div id="clientModalError" class="hidden mb-3 px-3 py-2 bg-red-100 text-red-700 rounded text-sm"></div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                <input type="text" id="ncl_name" class="w-full border rounded p-2 text-sm" placeholder="e.g. ACME Corporation">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="ncl_email" class="w-full border rounded p-2 text-sm" placeholder="info@company.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="text" id="ncl_website" class="w-full border rounded p-2 text-sm" placeholder="www.company.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                <select id="ncl_region" class="w-full border rounded p-2 text-sm" onchange="loadClientCountries(this.value)">
                    <option value="">-- Select Region --</option>
                    @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <select id="ncl_country" class="w-full border rounded p-2 text-sm">
                    <option value="">-- Select Region first --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                <select id="ncl_industry" class="w-full border rounded p-2 text-sm">
                    <option value="">-- Select Industry --</option>
                    @foreach($industries as $industry)
                    <option value="{{ $industry->id }}">{{ $industry->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tier</label>
                <select id="ncl_tier" class="w-full border rounded p-2 text-sm">
                    <option value="">-- Select Tier --</option>
                    @foreach($tiers as $tier)
                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Sales</label>
                <select id="ncl_sales" class="w-full border rounded p-2 text-sm">
                    <option value="">-- Select --</option>
                    @foreach($sales as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Engineer</label>
                <select id="ncl_engineer" class="w-full border rounded p-2 text-sm">
                    <option value="">-- Select --</option>
                    @foreach($engineers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <button type="button" onclick="closeAddClientModal()"
                class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                Cancel
            </button>
            <button type="button" onclick="saveNewClient()"
                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                Save Client
            </button>
        </div>
    </div>
</div>

{{-- ADD CONTACT MODAL --}}
<div id="addContactModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Add New Contact</h3>
            <button type="button" onclick="closeAddContactModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </div>

        <div id="contactModalError" class="hidden mb-3 px-3 py-2 bg-red-100 text-red-700 rounded text-sm"></div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input type="text" id="nc_firstname" class="w-full border rounded p-2 text-sm" placeholder="First name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" id="nc_lastname" class="w-full border rounded p-2 text-sm" placeholder="Last name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title / Position</label>
                <input type="text" id="nc_title" class="w-full border rounded p-2 text-sm" placeholder="e.g. Procurement Manager">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="nc_email" class="w-full border rounded p-2 text-sm" placeholder="email@example.com">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" id="nc_phone" class="w-full border rounded p-2 text-sm" placeholder="+961 1 234567">
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <button type="button" onclick="closeAddContactModal()"
                class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                Cancel
            </button>
            <button type="button" onclick="saveNewContact()"
                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                Save Contact
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>

let productIndex = 1;

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

let activityIndex = 0;

function buildActivityCard(i, data) {
    data = data || {};
    const type        = data.type || '';
    const name        = data.name || '';
    const date        = data.activity_date || '';
    const status      = data.status || 'Pending';
    const location    = data.location || '';
    const attendees   = data.attendees || '';
    const phone       = data.phone || '';
    const duration    = data.duration || '';
    const minutes     = data.minutes || '';

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

            {{-- Meeting-specific --}}
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

            {{-- Call-specific --}}
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

            {{-- Minutes / Notes (all types) --}}
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

    // Hide all conditional fields for this card
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
    // Trigger field visibility if type is pre-set
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
const oldContactId = "{{ old('contact_id') }}";

function filterContacts(companyId) {
    const select = document.getElementById('contactSelect');
    select.innerHTML = '<option value="">-- Select Contact --</option>';

    if (!companyId) {
        select.innerHTML = '<option value="">-- Select Client first --</option>';
        return;
    }

    const contacts = allContacts[companyId] || [];
    contacts.forEach(function(c) {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.firstname + ' ' + c.lastname;
        if (c.id == oldContactId) opt.selected = true;
        select.appendChild(opt);
    });
}

// Run on page load (for old() repopulation)
document.addEventListener('DOMContentLoaded', function () {
    toggleClosedFields();

    // If old company_id is set, populate contacts
    const companySelect = document.getElementById('companySelect');
    if (companySelect && companySelect.value) {
        filterContacts(companySelect.value);
    }
});

// ---- Add Client Modal ----

function openAddClientModal() {
    document.getElementById('addClientModal').classList.remove('hidden');
    document.getElementById('ncl_name').focus();
}

function closeAddClientModal() {
    document.getElementById('addClientModal').classList.add('hidden');
    ['ncl_name','ncl_email','ncl_website'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('clientModalError').classList.add('hidden');
}

function loadClientCountries(regionId) {
    const select = document.getElementById('ncl_country');
    select.innerHTML = '<option value="">Loading...</option>';
    if (!regionId) {
        select.innerHTML = '<option value="">-- Select Region first --</option>';
        return;
    }
    fetch(`/countries/by-region/${regionId}`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">-- Select Country --</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                select.appendChild(opt);
            });
        });
}

function saveNewClient() {
    const name       = document.getElementById('ncl_name').value.trim();
    const email      = document.getElementById('ncl_email').value.trim();
    const website    = document.getElementById('ncl_website').value.trim();
    const region_id  = document.getElementById('ncl_region').value;
    const country_id = document.getElementById('ncl_country').value;
    const industry_id= document.getElementById('ncl_industry').value;
    const tier_id    = document.getElementById('ncl_tier').value;
    const assigned_sales_id    = document.getElementById('ncl_sales').value;
    const assigned_engineer_id = document.getElementById('ncl_engineer').value;
    const errBox  = document.getElementById('clientModalError');

    if (!name) {
        errBox.textContent = 'Company name is required.';
        errBox.classList.remove('hidden');
        return;
    }

    errBox.classList.add('hidden');

    fetch('{{ route("companies.quickCreate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name, email, website, region_id, country_id, industry_id, tier_id, assigned_sales_id, assigned_engineer_id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            errBox.textContent = data.error;
            errBox.classList.remove('hidden');
            return;
        }

        // Add new client to the company dropdown and select it
        const select = document.getElementById('companySelect');
        const opt = document.createElement('option');
        opt.value = data.id;
        opt.textContent = data.name;
        opt.selected = true;
        select.appendChild(opt);

        // Trigger contact filter for the new company (empty contacts)
        filterContacts(data.id);

        closeAddClientModal();
    })
    .catch(() => {
        errBox.textContent = 'An error occurred. Please try again.';
        errBox.classList.remove('hidden');
    });
}

document.getElementById('addClientModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddClientModal();
});

// ---- Add Contact Modal ----

function openAddContactModal() {
    document.getElementById('addContactModal').classList.remove('hidden');
    document.getElementById('nc_firstname').focus();
}

function closeAddContactModal() {
    document.getElementById('addContactModal').classList.add('hidden');
    // Clear fields
    ['nc_firstname','nc_lastname','nc_title','nc_email','nc_phone'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('contactModalError').classList.add('hidden');
}

function saveNewContact() {
    const companyId = document.getElementById('companySelect').value;
    const firstname = document.getElementById('nc_firstname').value.trim();
    const lastname  = document.getElementById('nc_lastname').value.trim();
    const title     = document.getElementById('nc_title').value.trim();
    const email     = document.getElementById('nc_email').value.trim();
    const phone     = document.getElementById('nc_phone').value.trim();

    const errBox = document.getElementById('contactModalError');

    if (!firstname) {
        errBox.textContent = 'First name is required.';
        errBox.classList.remove('hidden');
        return;
    }

    errBox.classList.add('hidden');

    fetch('{{ route("contacts.quickCreate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ company_id: companyId, firstname, lastname, title, email, phone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            errBox.textContent = data.error;
            errBox.classList.remove('hidden');
            return;
        }

        // Add new contact to the dropdown and select it
        const select = document.getElementById('contactSelect');
        const opt = document.createElement('option');
        opt.value = data.id;
        opt.textContent = data.firstname + ' ' + (data.lastname || '');
        opt.selected = true;
        select.appendChild(opt);

        closeAddContactModal();
    })
    .catch(() => {
        errBox.textContent = 'An error occurred. Please try again.';
        errBox.classList.remove('hidden');
    });
}

// Close modal on backdrop click
document.getElementById('addContactModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddContactModal();
});

// ---- Excel Import ----

function importExcelProducts(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        if (rows.length <= 1) {
            alert('Excel file is empty or has only headers.');
            return;
        }

        const table = document.getElementById('productsTable');
        // Clear existing rows before importing
        table.innerHTML = '';
        productIndex = 0;

        rows.slice(1).forEach(row => {
            const partNumber = row[0] ?? '';
            const qty        = row[1] ?? '';
            const price      = row[2] ?? '';
            const moq        = row[3] ?? '';
            const mpq        = row[4] ?? '';
            const lead       = row[5] ?? '';
            const dateCode   = row[6] ?? '';

            const html = `
            <tr class="product-row">
                <td><input name="products[${productIndex}][part_number]" value="${partNumber}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][quantity]" type="number" value="${qty}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][unit_price]" type="number" step="0.0001" value="${price}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][moq]" type="number" value="${moq}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][mpq]" type="number" value="${mpq}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][lead_time]" value="${lead}" class="w-full border p-1"></td>
                <td><input name="products[${productIndex}][date_code]" value="${dateCode}" class="w-full border p-1"></td>
                <td class="text-center">
                    <button type="button" onclick="removeProduct(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                </td>
            </tr>`;

            table.insertAdjacentHTML('beforeend', html);
            productIndex++;
        });
    };
    reader.readAsArrayBuffer(file);
}

</script>

</x-app-layout>
