<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Create Contact
</h2>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm rounded-lg p-6">

<form method="POST" action="{{ route('contacts.store') }}">
@csrf

<div class="grid grid-cols-2 gap-6">

<div>
<label class="block text-sm font-medium text-gray-700">First Name</label>
<input type="text" name="firstname"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Last Name</label>
<input type="text" name="lastname"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Email</label>
<input type="email" name="email"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Phone</label>
<input type="text" name="phone"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Title</label>
<input type="text" name="title"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Company</label>
<select name="company_id"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="">Select Company</option>

@foreach($companies as $company)
<option value="{{ $company->id }}">
{{ $company->name }}
</option>
@endforeach

</select>
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Country</label>
<select name="country_id"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="">Select Country</option>

@foreach($countries as $country)
<option value="{{ $country->id }}">
{{ $country->name }}
</option>
@endforeach

</select>
</div>

</div>

<div class="mt-6 flex justify-end">
<button type="submit"
class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
Save Contact
</button>
</div>

</form>

</div>

</div>
</div>

</x-app-layout>