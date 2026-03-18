<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Contacts
</h2>

<a href="{{ route('contacts.create') }}"
class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
+ Add Contact
</a>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

<!-- FILTER BAR -->

<div class="flex gap-4 mb-6">

<input
type="text"
id="searchInput"
placeholder="Search contacts..."
class="border rounded px-3 py-2 w-64">

<select
id="companyFilter"
class="border rounded px-3 py-2">

<option value="">All Accounts</option>

@foreach($contacts->pluck('company_name')->unique() as $company)

<option value="{{ $company }}">
{{ $company }}
</option>

@endforeach

</select>

<button
onclick="resetFilters()"
class="px-4 py-2 bg-gray-400 text-white rounded">
Reset
</button>

</div>


<!-- CONTACT TABLE -->

<table class="min-w-full divide-y divide-gray-200" id="contactsTable">

<thead>
<tr>
<th class="px-4 py-2 text-left">First Name</th>
<th class="px-4 py-2 text-left">Last Name</th>
<th class="px-4 py-2 text-left">Email</th>
<th class="px-4 py-2 text-left">Phone</th>
<th class="px-4 py-2 text-left">Account</th>
</th>
<th class="px-4 py-2 text-left">Position</th>
<th class="px-4 py-2 text-left">Country</th>
<th class="px-4 py-2 text-left">Created</th>
<th class="px-4 py-2 text-left">Actions</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-200">

@forelse($contacts as $contact)

<tr>

<td class="px-4 py-2">
{{ $contact->firstname }}
</td>

<td class="px-4 py-2">
{{ $contact->lastname }}
</td>

<td class="px-4 py-2">
{{ $contact->email }}
</td>

<td class="px-4 py-2">
{{ $contact->phone }}
</td>

<td class="px-4 py-2 company">
{{ $contact->company_name }}
</td>

<td class="px-4 py-2">
{{ $contact->title }}
</td>

<td class="px-4 py-2">
{{ $contact->country_name }}
</td>

<td class="px-4 py-2">
{{ \Carbon\Carbon::parse($contact->created_at)->format('d M Y') }}
</td>

<td class="px-4 py-2">
<a href="{{ route('contacts.edit',$contact->id) }}"
class="text-blue-600">
Edit
</a>
</td>

</tr>

@empty

<tr>
<td colspan="9"
class="px-4 py-4 text-center text-gray-500">
No contacts found
</td>
</tr>

@endforelse

</tbody>

</table>

<!-- Pagination -->
<div class="mt-4">
{{ $contacts->links() }}
</div>

</div>
</div>
</div>

</x-app-layout>

<script>

const searchInput = document.getElementById("searchInput");
const companyFilter = document.getElementById("companyFilter");

searchInput.addEventListener("keyup", filterTable);
companyFilter.addEventListener("change", filterTable);


function filterTable(){

let search = searchInput.value.toLowerCase();
let company = companyFilter.value.toLowerCase();

let rows = document.querySelectorAll("#contactsTable tbody tr");

rows.forEach(row=>{

let text = row.innerText.toLowerCase();
let companyCell = row.querySelector(".company").innerText.toLowerCase();

let visible = true;

if(search && !text.includes(search))
visible = false;

if(company && companyCell !== company)
visible = false;

row.style.display = visible ? "" : "none";

});

}


function resetFilters(){

searchInput.value = "";
companyFilter.value = "";

filterTable();

}

</script>