<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Add Opportunity
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<form method="POST" action="{{ route('opportunities.store') }}">
@csrf

<div class="bg-white shadow rounded-lg p-6 mb-6">

<h3 class="text-lg font-bold mb-4">Opportunity Information</h3>

<div class="grid grid-cols-2 gap-6">

<div>
<label class="block text-sm font-medium">Company</label>
<select name="company_id" class="w-full border rounded p-2">
@foreach($companies as $company)
<option value="{{ $company->id }}">{{ $company->name }}</option>
@endforeach
</select>
</div>

<div>
<label class="block text-sm font-medium">Country</label>
<select name="country_id" class="w-full border rounded p-2">
@foreach($countries as $country)
<option value="{{ $country->id }}">{{ $country->name }}</option>
@endforeach
</select>
</div>

<div>
<label class="block text-sm font-medium">Project Application</label>
<input type="text" name="project_application" class="w-full border rounded p-2">
</div>

<div>
<label class="block text-sm font-medium">Status</label>
<select name="status" class="w-full border rounded p-2">
<option>New Lead</option>
</select>
</div>


<div>
<label>Assigned Sales</label>

<select name="assigned_sales_id" class="w-full border rounded p-2">
@foreach($sales as $user)
<option value="{{ $user->id }}">
{{ $user->name }}
</option>
@endforeach
</select>

</div>


<div>
<label>Assigned Engineer</label>

<select name="assigned_engineer_id" class="w-full border rounded p-2">
@foreach($engineers as $user)
<option value="{{ $user->id }}">
{{ $user->name }}
</option>
@endforeach
</select>

</div>

<div>
<label class="block text-sm font-medium">Notes</label>
<input type="text" name="notes" class="w-full border rounded p-2">
</div>

</div>

</div>


{{-- PRODUCTS --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">

<h3 class="text-lg font-bold mb-4">Products</h3>

<table class="w-full border text-sm">

<thead class="bg-gray-100">
<tr>
<th class="border p-2">Part Number</th>
<th class="border p-2">Qty</th>
<th class="border p-2">Unit Price</th>
<th class="border p-2">MOQ</th>
<th class="border p-2">MPQ</th>
<th class="border p-2">Lead Time</th>
<th class="border p-2">Date Code</th>
<th class="border p-2">Action</th>
</tr>
</thead>

<tbody id="productsTable">

<tr class="product-row">

<td>
<input name="products[0][part_number]" class="w-full border p-1">
</td>

<td>
<input name="products[0][quantity]" class="w-full border p-1">
</td>

<td>
<input name="products[0][unit_price]" class="w-full border p-1">
</td>

<td>
<input name="products[0][moq]" class="w-full border p-1">
</td>

<td>
<input name="products[0][mpq]" class="w-full border p-1">
</td>

<td>
<input name="products[0][lead_time]" class="w-full border p-1">
</td>

<td>
<input name="products[0][date_code]" class="w-full border p-1">
</td>

<td>
<button type="button" onclick="removeProduct(this)" class="text-red-500">
Remove
</button>
</td>

</tr>

</tbody>

</table>
</br>
<button type="button" onclick="addProduct()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">
+ Add Product
</button>

</div>


{{-- ACTIVITIES --}}
<div class="bg-white shadow rounded-lg p-6 mb-6">

<h3 class="text-lg font-bold mb-4">Activities</h3>

<table class="w-full border text-sm">

<thead class="bg-gray-100">
<tr>
<th class="border p-2">Activity</th>
<th class="border p-2">Date</th>
<th class="border p-2">Status</th>
<th class="border p-2">Action</th>
</tr>
</thead>

<tbody id="activitiesTable">

<tr>
<td><input name="activities[0][name]" class="w-full border p-1"></td>
<td><input type="date" name="activities[0][activity_date]" class="w-full border p-1"></td>
<td>
<select name="activities[0][status]" class="w-full border p-1">
<option>Pending</option>
</select>
</td>

<td>
<button type="button" onclick="removeActivity(this)" class="text-red-500">
Remove
</button>
</td>
</tr>

</tbody>

</table>
</br>
<button type="button" onclick="addActivity()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">
+ Add Activity
</button>

</div>


<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">
Save Opportunity
</button>

</form>

</div>
</div>

<script>
let productIndex = 1;

function addProduct() {

const table = document.getElementById('productsTable');

const row = `
<tr class="product-row">

<td>
<input name="products[${productIndex}][part_number]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][quantity]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][unit_price]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][moq]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][mpq]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][lead_time]" class="w-full border p-1">
</td>

<td>
<input name="products[${productIndex}][date_code]" class="w-full border p-1">
</td>

<td>
<button type="button" onclick="removeProduct(this)" class="text-red-500">
Remove
</button>
</td>

</tr>
`;

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
<tr>

<td>
<input name="activities[${activityIndex}][name]" class="w-full border p-1">
</td>

<td>
<input type="date" name="activities[${activityIndex}][activity_date]" class="w-full border p-1">
</td>

<td>
<select name="activities[${activityIndex}][status]" class="w-full border p-1">
<option>Pending</option>
</select>
</td>

<td>
<button type="button" onclick="removeActivity(this)" class="text-red-500">
Remove
</button>
</td>

</tr>
`;

table.insertAdjacentHTML('beforeend', row);

activityIndex++;

}
function removeActivity(button) {

button.closest('tr').remove();

}
</script>

</x-app-layout>