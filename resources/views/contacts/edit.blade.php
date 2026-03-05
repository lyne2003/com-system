<x-app-layout>

<x-slot name="header">
<div class="flex justify-between items-center">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Edit Contact
</h2>
</div>
</x-slot>

<div class="py-6">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm rounded-lg p-6">

<form method="POST" action="{{ route('contacts.update', $contact->id) }}">
@csrf
@method('PUT')

<div class="grid grid-cols-2 gap-6">

<div>
<label class="block text-sm font-medium text-gray-700">First Name</label>
<input type="text" name="firstname"
value="{{ $contact->firstname }}"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Last Name</label>
<input type="text" name="lastname"
value="{{ $contact->lastname }}"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Email</label>
<input type="email" name="email"
value="{{ $contact->email }}"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Phone</label>
<input type="text" name="phone"
value="{{ $contact->phone }}"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Position</label>
<input type="text" name="title"
value="{{ $contact->title }}"
class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Company</label>
<select name="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="">Select Company</option>

@foreach($companies as $company)
<option value="{{ $company->id }}"
{{ $contact->company_id == $company->id ? 'selected' : '' }}>
{{ $company->name }}
</option>
@endforeach

</select>
</div>

<div>
<label class="block text-sm font-medium text-gray-700">Country</label>
<select name="country_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

<option value="">Select Country</option>

@foreach($countries as $country)
<option value="{{ $country->id }}"
{{ $contact->country_id == $country->id ? 'selected' : '' }}>
{{ $country->name }}
</option>
@endforeach

</select>
</div>

</div>

<div class="mt-6 flex justify-end">
<button type="submit"
 class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
Update Contact
</button>
</div>

</form>

</div>

</div>
</div>

</x-app-layout>