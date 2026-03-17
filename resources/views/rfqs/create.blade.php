<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Add RFQ
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

    <form method="POST" action="{{ route('rfqs.store') }}">
    @csrf

    {{-- RFQ INFORMATION --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">RFQ Information</h3>

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Reference <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="reference"
                    value="{{ old('reference') }}"
                    class="w-full border rounded p-2 @error('reference') border-red-500 @enderror"
                    placeholder="e.g. RFQ-2026-001">
                @error('reference')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inquiry #</label>
                <input
                    type="text"
                    name="inquiry_n"
                    value="{{ old('inquiry_n') }}"
                    class="w-full border rounded p-2"
                    placeholder="e.g. INQ-001">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input
                    type="date"
                    name="date"
                    value="{{ old('date', date('Y-m-d')) }}"
                    class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select name="client_id" class="w-full border rounded p-2">
                    <option value="">-- Select Client --</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" @if(old('client_id') == $company->id) selected @endif>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" class="w-full border rounded p-2">
                    <option value="">-- Select Priority --</option>
                    <option value="Low" @if(old('priority')=='Low') selected @endif>Low</option>
                    <option value="Medium" @if(old('priority')=='Medium') selected @endif>Medium</option>
                    <option value="High" @if(old('priority')=='High') selected @endif>High</option>
                    <option value="Urgent" @if(old('priority')=='Urgent') selected @endif>Urgent</option>
                </select>
            </div>

        </div>

        <div class="grid grid-cols-2 gap-6 mt-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes to Purchasing</label>
                <textarea
                    name="notes_to_purchasing"
                    rows="3"
                    class="w-full border rounded p-2"
                    placeholder="Internal notes for purchasing team...">{{ old('notes_to_purchasing') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes to Elias</label>
                <textarea
                    name="notes_to_elias"
                    rows="3"
                    class="w-full border rounded p-2"
                    placeholder="Notes for Elias...">{{ old('notes_to_elias') }}</textarea>
            </div>

        </div>

    </div>

    {{-- ITEMS --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h3 class="text-lg font-bold mb-4 text-gray-800">Items</h3>

        {{-- Excel Import --}}
        <div class="mb-4 flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
            <input
                type="file"
                id="excelFile"
                accept=".xlsx,.xls"
                class="border p-2 rounded text-sm"
                onchange="importExcelItems(event)">
            <span class="text-sm text-gray-500">
                Excel columns order: <strong>Line # | Overall Code | Part Number | Qty | UOM | Target Price | Manufacturer Name</strong>
            </span>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left text-gray-600 w-16">Line #</th>
                    <th class="border p-2 text-left text-gray-600">Overall Code</th>
                    <th class="border p-2 text-left text-gray-600">Part Number <span class="text-red-400">*</span></th>
                    <th class="border p-2 text-left text-gray-600 w-20">Qty</th>
                    <th class="border p-2 text-left text-gray-600 w-20">UOM</th>
                    <th class="border p-2 text-left text-gray-600 w-28">Target Price</th>
                    <th class="border p-2 text-left text-gray-600">Manufacturer</th>
                    <th class="border p-2 text-center text-gray-600 w-20">Action</th>
                </tr>
            </thead>
            <tbody id="itemsTable">
                <tr class="item-row">
                    <td><input name="items[0][line_number]" value="1" class="w-full border p-1 text-center" readonly></td>
                    <td><input name="items[0][overallcode]" class="w-full border p-1"></td>
                    <td><input name="items[0][partnumber]" class="w-full border p-1" placeholder="Required"></td>
                    <td><input name="items[0][qty]" type="number" class="w-full border p-1"></td>
                    <td><input name="items[0][uom]" class="w-full border p-1" placeholder="pcs"></td>
                    <td><input name="items[0][target_price]" type="number" step="0.01" class="w-full border p-1"></td>
                    <td>
                        <select name="items[0][manufacturer_id]" class="w-full border p-1">
                            <option value="">-- Select --</option>
                            @foreach($manufacturers as $mfr)
                            <option value="{{ $mfr->id }}">{{ $mfr->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="mt-3">
            <button type="button" onclick="addItem()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-sm text-sm">
                + Add Item
            </button>
        </div>

    </div>

    {{-- SUBMIT --}}
    <div class="flex gap-3">
        <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-2 rounded-lg shadow-md">
            Save RFQ
        </button>
        <a href="{{ route('rfqs.index') }}"
            class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-2 rounded-lg shadow-md">
            Cancel
        </a>
    </div>

    </form>

</div>
</div>

<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>

let itemIndex = 1;

// Manufacturer options HTML (for dynamically added rows)
const manufacturerOptions = `<option value="">-- Select --</option>` +
    @json($manufacturers->map(fn($m) => ['id' => $m->id, 'name' => $m->name]))
    .map(m => `<option value="${m.id}">${m.name}</option>`)
    .join('');

function addItem() {
    const table = document.getElementById('itemsTable');
    const lineNum = table.rows.length + 1;

    const row = `
    <tr class="item-row">
        <td><input name="items[${itemIndex}][line_number]" value="${lineNum}" class="w-full border p-1 text-center" readonly></td>
        <td><input name="items[${itemIndex}][overallcode]" class="w-full border p-1"></td>
        <td><input name="items[${itemIndex}][partnumber]" class="w-full border p-1" placeholder="Required"></td>
        <td><input name="items[${itemIndex}][qty]" type="number" class="w-full border p-1"></td>
        <td><input name="items[${itemIndex}][uom]" class="w-full border p-1" placeholder="pcs"></td>
        <td><input name="items[${itemIndex}][target_price]" type="number" step="0.01" class="w-full border p-1"></td>
        <td>
            <select name="items[${itemIndex}][manufacturer_id]" class="w-full border p-1">
                ${manufacturerOptions}
            </select>
        </td>
        <td class="text-center">
            <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
        </td>
    </tr>`;

    table.insertAdjacentHTML('beforeend', row);
    itemIndex++;
    updateLineNumbers();
}

function removeItem(button) {
    button.closest('tr').remove();
    updateLineNumbers();
}

function updateLineNumbers() {
    const rows = document.querySelectorAll('#itemsTable tr.item-row');
    rows.forEach((row, index) => {
        const lineInput = row.querySelector('input[name*="[line_number]"]');
        if (lineInput) lineInput.value = index + 1;
    });
}

function importExcelItems(event) {
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

        const table = document.getElementById('itemsTable');

        // Clear existing rows
        table.innerHTML = '';
        itemIndex = 0;

        rows.slice(1).forEach((row, idx) => {
            const lineNum  = row[0] ?? (idx + 1);
            const overall  = row[1] ?? '';
            const partNum  = row[2] ?? '';
            const qty      = row[3] ?? '';
            const uom      = row[4] ?? '';
            const price    = row[5] ?? '';
            // Manufacturer by name — we just put it in a text input since we can't match by name easily
            const mfrName  = row[6] ?? '';

            const html = `
            <tr class="item-row">
                <td><input name="items[${itemIndex}][line_number]" value="${lineNum}" class="w-full border p-1 text-center" readonly></td>
                <td><input name="items[${itemIndex}][overallcode]" value="${overall}" class="w-full border p-1"></td>
                <td><input name="items[${itemIndex}][partnumber]" value="${partNum}" class="w-full border p-1"></td>
                <td><input name="items[${itemIndex}][qty]" type="number" value="${qty}" class="w-full border p-1"></td>
                <td><input name="items[${itemIndex}][uom]" value="${uom}" class="w-full border p-1"></td>
                <td><input name="items[${itemIndex}][target_price]" type="number" step="0.01" value="${price}" class="w-full border p-1"></td>
                <td>
                    <select name="items[${itemIndex}][manufacturer_id]" class="w-full border p-1">
                        ${manufacturerOptions}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" onclick="removeItem(this)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                </td>
            </tr>`;

            table.insertAdjacentHTML('beforeend', html);
            itemIndex++;
        });
    };

    reader.readAsArrayBuffer(file);
}

</script>

</x-app-layout>
