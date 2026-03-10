<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Edit Opportunity
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<form method="POST" action="{{ route('opportunities.update',$opportunity->id) }}">
@csrf

<div class="bg-white shadow rounded-lg p-6 mb-6">

<h3 class="text-lg font-bold mb-4">Opportunity Information</h3>

<div class="grid grid-cols-2 gap-6">

<div>
<label class="block text-sm font-medium">Company</label>

<select name="company_id" class="w-full border rounded p-2">

@foreach($companies as $company)

<option value="{{ $company->id }}"
@if($company->id == $opportunity->company_id) selected @endif>

{{ $company->name }}

</option>

@endforeach

</select>

</div>


<div>
<label class="block text-sm font-medium">Country</label>

<select name="country_id" class="w-full border rounded p-2">

@foreach($countries as $country)

<option value="{{ $country->id }}"
@if($country->id == $opportunity->country_id) selected @endif>

{{ $country->name }}

</option>

@endforeach

</select>

</div>


<div>
<label class="block text-sm font-medium">Project Application</label>

<input
type="text"
name="project_application"
value="{{ $opportunity->project_application }}"
class="w-full border rounded p-2">

</div>


<div>
<label class="block text-sm font-medium">Status</label>

<select name="status" id="statusSelect" class="w-full border rounded p-2">

<option value="New Lead"
@if($opportunity->status == "New Lead") selected @endif>
New Lead
</option>

<option value="Closed Won"
@if($opportunity->status == "Closed Won") selected @endif>
Closed Won
</option>

<option value="Closed Lost"
@if($opportunity->status == "Closed Lost") selected @endif>
Closed Lost
</option>

</select>
<div id="closedWonBox" style="display:none">

<label class="block text-sm font-medium">
Closed Won %
</label>

<input
type="number"
name="closed_won_percentage"
value="{{ $opportunity->closed_won_percentage }}"
class="w-full border rounded p-2"
placeholder="Example: 80">

</div>
<div id="closedLostBox" style="display:none">

<label class="block text-sm font-medium">
Closed Lost Reason
</label>

<select name="closed_lost_reason" class="w-full border rounded p-2">

<option value="">Select reason</option>

<option value="Price too high"
@if($opportunity->closed_lost_reason == "Price too high") selected @endif>
Price too high
</option>

<option value="No stock"
@if($opportunity->closed_lost_reason == "No stock") selected @endif>
No stock
</option>

<option value="Competitor"
@if($opportunity->closed_lost_reason == "Competitor") selected @endif>
Competitor
</option>

<option value="Project canceled"
@if($opportunity->closed_lost_reason == "Project canceled") selected @endif>
Project canceled
</option>

<option value="Client not responding"
@if($opportunity->closed_lost_reason == "Client not responding") selected @endif>
Client not responding
</option>

<option value="Technical rejection"
@if($opportunity->closed_lost_reason == "Technical rejection") selected @endif>
Technical rejection
</option>

<option value="Other"
@if($opportunity->closed_lost_reason == "Other") selected @endif>
Other
</option>

</select>

</div>
</div>


<div>

<label>Assigned Sales</label>

<select name="assigned_sales_id" class="w-full border rounded p-2">

@foreach($sales as $user)

<option value="{{ $user->id }}"
@if($user->id == $opportunity->assigned_sales_id) selected @endif>

{{ $user->name }}

</option>

@endforeach

</select>

</div>


<div>

<label>Assigned Engineer</label>

<select name="assigned_engineer_id" class="w-full border rounded p-2">

@foreach($engineers as $user)

<option value="{{ $user->id }}"
@if($user->id == $opportunity->assigned_engineer_id) selected @endif>

{{ $user->name }}

</option>

@endforeach

</select>

</div>


<div>
<label class="block text-sm font-medium">Notes</label>

<input
type="text"
name="notes"
value="{{ $opportunity->notes }}"
class="w-full border rounded p-2">

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

@foreach($products as $index => $product)

<tr class="product-row">

<td>
<input name="products[{{ $index }}][part_number]"
value="{{ $product->part_number }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][quantity]"
value="{{ $product->quantity }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][unit_price]"
value="{{ $product->unit_price }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][moq]"
value="{{ $product->moq }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][mpq]"
value="{{ $product->mpq }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][lead_time]"
value="{{ $product->lead_time }}"
class="w-full border p-1">
</td>

<td>
<input name="products[{{ $index }}][date_code]"
value="{{ $product->date_code }}"
class="w-full border p-1">
</td>

<td>
<button type="button"
onclick="removeProduct(this)"
class="text-red-500">
Remove
</button>
</td>

</tr>

@endforeach

</tbody>

</table>

</br>

<button
type="button"
onclick="addProduct()"
class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">

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

@foreach($activities as $index => $activity)

<tr>

<td>
<input
name="activities[{{ $index }}][name]"
value="{{ $activity->name }}"
class="w-full border p-1">
</td>

<td>
<input
type="date"
name="activities[{{ $index }}][activity_date]"
value="{{ $activity->activity_date }}"
class="w-full border p-1">
</td>

<td>

<select
name="activities[{{ $index }}][status]"
class="w-full border p-1">

<option
@if($activity->status=="Pending") selected @endif>
Pending
</option>

<option
@if($activity->status=="Completed") selected @endif>
Completed
</option>

</select>

</td>

<td>

<button
type="button"
onclick="removeActivity(this)"
class="text-red-500">

Remove

</button>

</td>

</tr>

@endforeach

</tbody>

</table>

</br>

<button
type="button"
onclick="addActivity()"
class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">

+ Add Activity

</button>

</div>



<button
type="submit"
class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md">

Update Opportunity

</button>

</form>

</div>
</div>



<script>

let productIndex = {{ count($products) }};

function addProduct(){

const table = document.getElementById('productsTable');

const row = `
<tr class="product-row">

<td><input name="products[${productIndex}][part_number]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][quantity]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][unit_price]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][moq]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][mpq]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][lead_time]" class="w-full border p-1"></td>
<td><input name="products[${productIndex}][date_code]" class="w-full border p-1"></td>

<td>
<button type="button" onclick="removeProduct(this)" class="text-red-500">
Remove
</button>
</td>

</tr>
`;

table.insertAdjacentHTML('beforeend',row);

productIndex++;

}

function removeProduct(button){

button.closest('tr').remove();

}


let activityIndex = {{ count($activities) }};

function addActivity(){

const table = document.getElementById('activitiesTable');

const row = `
<tr>

<td><input name="activities[${activityIndex}][name]" class="w-full border p-1"></td>

<td><input type="date" name="activities[${activityIndex}][activity_date]" class="w-full border p-1"></td>

<td>
<select name="activities[${activityIndex}][status]" class="w-full border p-1">
<option>Pending</option>
<option>Completed</option>
</select>
</td>

<td>
<button type="button" onclick="removeActivity(this)" class="text-red-500">
Remove
</button>
</td>

</tr>
`;

table.insertAdjacentHTML('beforeend',row);

activityIndex++;

}

function removeActivity(button){

button.closest('tr').remove();

}
const statusSelect = document.getElementById('statusSelect');
const closedWonBox = document.getElementById('closedWonBox');
const closedLostBox = document.getElementById('closedLostBox');

function toggleClosedFields(){

const status = statusSelect.value;

closedWonBox.style.display = "none";
closedLostBox.style.display = "none";

if(status === "Closed Won"){
closedWonBox.style.display = "block";
}

if(status === "Closed Lost"){
closedLostBox.style.display = "block";
}

}

statusSelect.addEventListener("change", toggleClosedFields);

// run on page load
toggleClosedFields();
</script>

</x-app-layout>