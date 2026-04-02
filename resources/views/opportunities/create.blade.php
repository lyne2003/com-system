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
                <select name="company_id" id="companySelect" class="w-full border rounded p-2"
                        onchange="filterContacts(this.value)">
                    <option value="">-- Select Client --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" @if(old('company_id') == $company->id) selected @endif>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <select name="contact_id" id="contactSelect" class="w-full border rounded p-2">
                    <option value="">-- Select Client first --</option>
                </select>
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

        <div class="overflow-x-auto">
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Activity</th>
                    <th class="border p-2 text-left">Date</th>
                    <th class="border p-2 text-left">Status</th>
                    <th class="border p-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody id="activitiesTable">
                <tr class="activity-row">
                    <td><input name="activities[0][name]" class="w-full border p-1" placeholder="e.g. Follow-up call"></td>
                    <td><input type="date" name="activities[0][activity_date]" class="w-full border p-1"></td>
                    <td>
                        <select name="activities[0][status]" class="w-full border p-1">
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" onclick="removeActivity(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
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

let activityIndex = 1;

function addActivity() {
    const table = document.getElementById('activitiesTable');
    const row = `
    <tr class="activity-row">
        <td><input name="activities[${activityIndex}][name]" class="w-full border p-1" placeholder="e.g. Follow-up call"></td>
        <td><input type="date" name="activities[${activityIndex}][activity_date]" class="w-full border p-1"></td>
        <td>
            <select name="activities[${activityIndex}][status]" class="w-full border p-1">
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" onclick="removeActivity(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
        </td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
    activityIndex++;
}

function removeActivity(button) {
    button.closest('tr').remove();
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
