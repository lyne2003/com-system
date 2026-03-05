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

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">First Name</th>
                            <th class="px-4 py-2 text-left">Last Name</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Phone</th>
                            <th class="px-4 py-2 text-left">Company</th>
                            <th class="px-4 py-2 text-left">Position</th>
                            <th class="px-4 py-2 text-left">Country</th>
                            <th class="px-4 py-2 text-left">Created</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($contacts as $contact)
                            <tr>
                                <td class="px-4 py-2">{{ $contact->firstname }}</td>
                                <td class="px-4 py-2">{{ $contact->lastname }}</td>
                                <td class="px-4 py-2">{{ $contact->email }}</td>
                                <td class="px-4 py-2">{{ $contact->phone }}</td>
                                <td class="px-4 py-2">{{ $contact->company_name }}</td>
                                <td class="px-4 py-2">{{ $contact->title }}</td>
                                <td class="px-4 py-2">{{ $contact->country_name }}</td>
                                <td class="px-4 py-2">
                                    {{ \Carbon\Carbon::parse($contact->created_at)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('contacts.edit', $contact->id) }}">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                                    No contacts found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>