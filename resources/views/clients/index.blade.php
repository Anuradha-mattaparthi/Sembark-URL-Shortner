{{-- Displaying all clients for admin and superadmin when clicking on view more button from dashboards --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">
                @if($mode === 'superadmin')
                    All Clients
                @else
                    Company Users
                @endif
            </h2>

            <a href="{{ auth()->user()->role === 'admin' ? route('admindashboard') : route('dashboard') }}"
                class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">
                 ← Back
             </a>

        </div>
    </x-slot>

    <div class="py-12 flex justify-center">
        <div class="w-full max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">

                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>

                            @if($mode === 'superadmin')
                                <th class="px-4 py-3 text-left text-sm font-semibold">Company</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Users</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Total URLs</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Total Hits</th>
                            @else
                                <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Role</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Total URLs</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Total Hits</th>
                            @endif

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        {{-- SUPER ADMIN VIEW --}}
                        @if($mode === 'superadmin')
                            @foreach ($clients as $client)
                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-3">
                                        <div class="font-semibold">{{ $client->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $client->admin->email ?? 'No admin assigned' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">{{ $client->users_count }}</td>
                                    <td class="px-4 py-3 text-center">{{ $client->short_urls_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center"> {{ $client->short_urls_sum_hits ?? 0 }}</td>

                                </tr>
                            @endforeach
                        @endif


                        {{-- ADMIN VIEW --}}
                        @if($mode === 'admin')
                            @foreach ($clients as $client)
                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-3 font-semibold">{{ $client->name }}</td>
                                    <td class="px-4 py-3">{{ $client->email }}</td>
                                    <td class="px-4 py-3 text-center capitalize">{{ $client->role }}</td>
                                    <td class="px-4 py-3 text-center">{{ $client->short_urls_count ?? 0}}</td>
                                    <td class="px-4 py-3 text-center">{{ $client->short_urls_sum_hits ?? 0 }}</td>

                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
