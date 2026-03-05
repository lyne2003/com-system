<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Companies') }}
            </h2>

            <a href="{{ route('companies.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                + Add Company
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Website</th>
                            <th class="px-4 py-2 text-left">Country</th>
                            <th class="px-4 py-2 text-left">Region</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Industry</th>
                            <th class="px-4 py-2 text-left">Tier</th>
                            <th class="px-4 py-2 text-left">Assigned Sales</th>
                            <th class="px-4 py-2 text-left">Assigned Engineer</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($companies as $company)
                            <tr>
                                <td class="px-4 py-2">{{ $company->name }}</td>
                                <td class="px-4 py-2">{{ $company->email }}</td>
                                <td class="px-4 py-2">{{ $company->website }}</td>
                                <td class="px-4 py-2">{{ $company->country_name }}</td>
                                <td class="px-4 py-2">{{ $company->region_name }}</td>
                                <td class="px-4 py-2">{{ $company->type_name }}</td>
                                <td class="px-4 py-2">{{ $company->industry_name }}</td>
                                <td class="px-4 py-2">{{ $company->tier_name }}</td>
                                <td class="px-4 py-2">{{ $company->assigned_sales_name }}</td>
                                <td class="px-4 py-2">{{ $company->assigned_engineer_name }}</td>
                                <td class="px-4 py-2">{{ $company->status_name }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('companies.edit', $company->id) }}">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                                    No companies found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</x-app-layout>