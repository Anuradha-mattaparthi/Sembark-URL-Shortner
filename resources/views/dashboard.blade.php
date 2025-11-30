{{-- Super admin dashboard--}}


<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Welcome Super Admin!

            </h2>
        </div>
    </x-slot>

    @php
        // prepare visible/hidden clients locally in the view
        $visibleClients = isset($clients) ? $clients->take(2) : collect();
        $hiddenClients  = isset($clients) ? $clients->slice(2) : collect();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Clients</h2>

                        <button
                            x-data
                            @click="$dispatch('open-invite')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow"
                        >
                            + Invite New Company Admin
                        </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Company</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Users</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Total URLs</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Total Hits</th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($visibleClients as $client)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $client->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $client->admin->email ?? 'No admin assigned' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center text-gray-700">
                                        {{ $client->users_count ?? 0 }}
                                    </td>

                                    <td class="px-4 py-3 text-center text-gray-700"> {{ $client->short_urls_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700"> {{ $client->short_urls_sum_hits ?? 0 }}</td>


                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        No clients found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hiddenClients->count())
                    <div class="mt-4">
                        <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded inline-block">
                            View More
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
    <div
        x-data="{ openInvite: false, showToast: false }"
        x-init="
            // Listen for click from invite button (which dispatches event)
            window.addEventListener('open-invite', () => openInvite = true);

            // Auto-open on validation errors
            @if($errors->any())
                openInvite = true;
            @endif

            // Show toast when session success exists
            @if(session('success'))
                showToast = true;
                setTimeout(() => showToast = false, 4000);
            @endif
        "
    >
        <div
            x-show="openInvite"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center"
        >
            <div
                x-show="openInvite"
                x-transition
                x-on:click.away="openInvite = false"
                class="bg-white w-full max-w-md mx-4 rounded-lg shadow-xl transform transition-all"
                role="dialog" aria-modal="true"
            >
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <h3 class="text-lg font-semibold">Invite New Company Admin</h3>
                        <button @click="openInvite = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('super.invite.send') }}" class="mt-4">
                        @csrf

                        <div class="mb-3">
                            <label class="block text-sm font-medium">Company Name</label>
                            <input name="company_name" value="{{ old('company_name') }}" required class="mt-1 block w-full border rounded px-3 py-2" />
                            @error('company_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium">Admin Name</label>
                            <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full border rounded px-3 py-2" />
                            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium">Admin Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border rounded px-3 py-2" />
                            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="openInvite = false" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Toast popup (bottom-right) --}}
        <div
            x-show="showToast"
            x-transition
            x-cloak
            class="fixed right-4 bottom-4 z-50"
            style="pointer-events: none;"
        >
            <div class="bg-green-600 text-white px-4 py-2 rounded shadow">
                {{ session('success') ?? 'Saved' }}
            </div>
        </div>
    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-semibold mb-3">Generated URLs</h3>


                <div class="overflow-x-auto border rounded">
                    <table id="shortUrlTable" class="w-full min-w-[700px] border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-3 text-left">Long URL</th>
                                <th class="border p-3 text-left">Short URL</th>
                                <th class="border p-3 text-center">Hits</th>
                                <th class="border p-3 text-left">Created On</th>
                                <th class="border p-3 text-left">Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visibleUrls as $row)
                                <tr class="even:bg-white odd:bg-gray-50">
                                    <td class="border p-3 align-top break-words max-w-[420px]">
                                        <a href="{{ $row->long_url }}" target="_blank" rel="noopener" class="text-blue-600 underline break-all">
                                            {{ $row->long_url }}
                                        </a>
                                    </td>

                                    <td class="border p-3 align-top break-words max-w-[260px]">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate">{{ $shortDomain }}/r/{{ $row->short_code }}</span>
                                            <button data-copy="{{ $shortDomain }}/r/{{ $row->short_code }}" class="copy-btn px-2 py-1 border rounded text-sm">Copy</button>
                                            <a href="{{ $shortDomain }}/r/{{ $row->short_code }}" target="_blank" rel="noopener" class="px-2 py-1 bg-indigo-600 text-white rounded text-sm">Open</a>
                                        </div>
                                    </td>

                                    <td class="border p-3 text-center align-top">{{ $row->hits }}</td>

                                    <td class="border p-3 align-top">
                                        {{ $row->created_at->format('d M Y h:i A') }}
                                    </td>

                                    <td class="border p-3 align-top">
                                        {{ $row->company->name ?? '—' }} <div class="text-xs text-gray-500">({{ $row->user->email ?? '—' }})</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($hiddenUrls->count())
                    <div class="mt-4">
                        <a href="{{ route('superadmin.urls.all') }}" class="px-4 py-2 bg-blue-600 text-white rounded inline-block">View More</a>
                    </div>
                @endif

            </div>
        </div>
    </div>
    <style>
        @keyframes pop {
            from { transform: translateY(-6px) scale(0.98); opacity: 0; }
            to   { transform: translateY(0) scale(1); opacity: 1; }
        }
    </style>
</x-app-layout>
