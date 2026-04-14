<x-app-layout>
<x-slot name="header">
<div class="flex justify-between items-center">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
{{ __('Accounts') }}
</h2>

<a href="{{ route('companies.create') }}"
class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
+ Add Account
</a>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

<div class="flex flex-wrap gap-4 mb-6">

<input
type="text"
id="searchInput"
placeholder="Search companies..."
class="border rounded px-3 py-2 w-64">

<select id="typeFilter" class="border rounded px-3 py-2">
<option value="">All Types</option>
@foreach($companies->pluck('type_name')->unique() as $type)
<option value="{{ $type }}">{{ $type }}</option>
@endforeach
</select>

<select id="tierFilter" class="border rounded px-3 py-2">
<option value="">All Tiers</option>
@foreach($companies->pluck('tier_name')->unique() as $tier)
<option value="{{ $tier }}">{{ $tier }}</option>
@endforeach
</select>

<select id="salesFilter" class="border rounded px-3 py-2">
<option value="">All Sales</option>
@foreach($companies->pluck('assigned_sales_name')->unique() as $sales)
<option value="{{ $sales }}">{{ $sales }}</option>
@endforeach
</select>

<select id="engineerFilter" class="border rounded px-3 py-2">
<option value="">All Engineers</option>
@foreach($companies->pluck('assigned_engineer_name')->unique() as $engineer)
<option value="{{ $engineer }}">{{ $engineer }}</option>
@endforeach
</select>

<button
onclick="resetFilters()"
class="px-4 py-2 bg-gray-400 text-white rounded">
Reset
</button>

</div>

<!-- TABLE -->

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200 text-sm" id="companiesTable">

<thead class="bg-gray-50">
<tr>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Name</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Email</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Website</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Country</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Region</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Type</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Industry</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Tier</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Assigned Sales</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Assigned Engineer</th>
<th class="px-4 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">Actions</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-100">

@forelse($companies as $company)

<tr class="hover:bg-gray-50">

<td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $company->name }}</td>
<td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $company->email }}</td>
<td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $company->website }}</td>
<td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $company->country_name }}</td>
<td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $company->region_name }}</td>
<td class="px-4 py-3 type whitespace-nowrap">{{ $company->type_name }}</td>
<td class="px-4 py-3 text-gray-600">{{ $company->industry_name }}</td>
<td class="px-4 py-3 tier whitespace-nowrap">{{ $company->tier_name }}</td>
<td class="px-4 py-3 sales whitespace-nowrap">{{ $company->assigned_sales_name }}</td>
<td class="px-4 py-3 engineer whitespace-nowrap">{{ $company->assigned_engineer_name }}</td>

<td class="px-4 py-3 whitespace-nowrap">
<div class="flex items-center gap-3">
    <a href="{{ route('companies.edit', $company->id) }}"
       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded">Edit</a>

    <form method="POST" action="{{ route('companies.destroy', $company->id) }}"
          onsubmit="return confirm('Delete this account?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded">Delete</button>
    </form>
</div>
</td>

</tr>

@empty

<tr>
<td colspan="12" class="px-4 py-4 text-center text-gray-500">
No companies found
</td>
</tr>

@endforelse

</tbody>

</table>

<!-- Pagination -->
<div class="mt-4">
{{ $companies->links() }}
</div>

</div>
</div>
</div>

</x-app-layout>

<script>

const searchInput = document.getElementById("searchInput");
const typeFilter = document.getElementById("typeFilter");
const tierFilter = document.getElementById("tierFilter");
const salesFilter = document.getElementById("salesFilter");
const engineerFilter = document.getElementById("engineerFilter");

searchInput.addEventListener("keyup", filterTable);
typeFilter.addEventListener("change", filterTable);
tierFilter.addEventListener("change", filterTable);
salesFilter.addEventListener("change", filterTable);
engineerFilter.addEventListener("change", filterTable);

function filterTable(){

let search = searchInput.value.toLowerCase();
let type = typeFilter.value.toLowerCase();
let tier = tierFilter.value.toLowerCase();
let sales = salesFilter.value.toLowerCase();
let engineer = engineerFilter.value.toLowerCase();

let rows = document.querySelectorAll("#companiesTable tbody tr");

rows.forEach(row=>{

let text = row.innerText.toLowerCase();

let typeCell = row.querySelector(".type").innerText.toLowerCase();
let tierCell = row.querySelector(".tier").innerText.toLowerCase();
let salesCell = row.querySelector(".sales").innerText.toLowerCase();
let engineerCell = row.querySelector(".engineer").innerText.toLowerCase();

let visible = true;

if(search && !text.includes(search)) visible = false;
if(type && typeCell !== type) visible = false;
if(tier && tierCell !== tier) visible = false;
if(sales && salesCell !== sales) visible = false;
if(engineer && engineerCell !== engineer) visible = false;

row.style.display = visible ? "" : "none";

});

}

function resetFilters(){

searchInput.value = "";
typeFilter.value = "";
tierFilter.value = "";
salesFilter.value = "";
engineerFilter.value = "";

filterTable();

}

</script>