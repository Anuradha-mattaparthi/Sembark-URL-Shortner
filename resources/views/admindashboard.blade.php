{{-- Admin dashboard --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome Admin to {{ $companyName }}
            </h2>
        </div>
    </x-slot>

    @php
        //
        $visibleClients = $visibleClients ?? ($clients->take(2) ?? collect());
        $hiddenClients  = $hiddenClients  ?? ($clients->slice(2) ?? collect());
        // Provide a config fallback for short domain. Add 'short_domain' key to config/app.php if you want.
        $shortDomain = env('SHORT_DOMAIN', config('app.url'));


    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Clients</h2>

                    <button
                    x-data
                    @click="$dispatch('open-admin-invite')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow"
                >
                    + Invite User
                </button>

                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Role</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Total URLs</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Total Hits</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($visibleClients as $client)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $client->name }}</div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="text-xs text-gray-500">{{ $client->email ?? 'No admin assigned' }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-center text-gray-700">{{ ucfirst($client->role) }}</td>


                                    <td class="px-4 py-3 text-center text-gray-700">{{ $client->short_urls_count ?? 0 }}</td>

                                    <td class="px-4 py-3 text-center text-gray-700">{{ $client->short_urls_sum_hits ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No clients found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($hiddenClients->count())
                    <div class="mt-4">
                        <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded inline-block">View More</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Provide server values safely to JS --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const SHORT_DOMAIN = {!! json_encode($shortDomain) !!};
        const ROUTES = {
            allUrls: {!! json_encode(route('admin.urls.all')) !!},
            createShort: {!! json_encode(route('short-urls.store')) !!}
        };
        const CURRENT_USER = {!! json_encode(auth()->user() ? auth()->user()->only(['name','email']) : null) !!};
    </script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <div class="flex justify-end mb-4">
                    <button id="openModalBtn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">
                        Generate URL
                    </button>
                </div>

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
                                        {{ $row->user->name ?? '—' }} <div class="text-xs text-gray-500">({{ $row->user->email ?? '—' }})</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($hiddenUrls->count())
                    <div class="mt-4">
                        <a href="{{ route('admin.urls.all') }}" class="px-4 py-2 bg-blue-600 text-white rounded inline-block">View More</a>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="shortUrlModal" role="dialog" aria-modal="true" aria-labelledby="shortUrlModalTitle" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-lg p-6 shadow relative">
            <button id="closeModalBtn" aria-label="Close dialog" class="absolute top-2 right-3 text-gray-600 text-2xl">&times;</button>

            <h2 id="shortUrlModalTitle" class="text-lg font-semibold mb-4">Generate Short URL</h2>

            <form id="shortUrlForm" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium">Long URL</label>
                <input type="url" id="long_url" name="long_url" required class="mt-1 w-full border rounded p-2"
                       placeholder="https://example.com/page" aria-label="Long URL">

                <p id="error" class="text-red-600 text-sm mt-2 hidden" role="alert"></p>

                <div class="flex justify-end">
                    <button type="submit" class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded">Generate Short URL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Simple toast area -->
    <div id="toast" class="fixed top-6 right-6 z-60 hidden"></div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- AJAX LOGIC -->
    <script>
        // Date formatting (browser local timezone)
        function formatDate(dt) {
            if (!dt) return '';
            const date = new Date(dt);
            return new Intl.DateTimeFormat('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            }).format(date);
        }

        // Small toast helper
        function showToast(message, timeout = 2500) {
            const $t = $('#toast');
            $t.text(message).removeClass('hidden').addClass('block bg-black/80 text-white px-4 py-2 rounded');
            setTimeout(() => $t.addClass('hidden').removeClass('block'), timeout);
        }

        $(function () {
            const token = $('meta[name="csrf-token"]').attr("content");

            const $modal = $("#shortUrlModal");
            const $openBtn = $("#openModalBtn");
            const $closeBtn = $("#closeModalBtn");
            const $form = $("#shortUrlForm");
            const $longUrl = $("#long_url");
            const $error = $("#error");
            const $tbody = $("#shortUrlTable tbody");

            /* Modal functions with focus management */
            function showModal() {
                $modal.removeClass("hidden").addClass("flex");
                $error.hide().text("");
                $longUrl.val("").focus();
            }
            function hideModal() {
                $modal.addClass("hidden").removeClass("flex");
                $openBtn.focus();
            }
            $openBtn.on("click", showModal);
            $closeBtn.on("click", hideModal);
            $modal.on("click", (e) => { if (e.target === $modal[0]) hideModal(); });

            /* Populate a single row safely using jQuery builders */
            function buildRow(row) {
                const shortUrl = `${SHORT_DOMAIN}/r/${encodeURIComponent(row.short_code)}`;
                const longUrl = row.long_url || '';

                const $tr = $('<tr>').addClass('even:bg-white odd:bg-gray-50');

                // Long URL cell
                const $longCell = $('<td>').addClass('border p-3 align-top break-words max-w-[420px]');
                const $longLink = $('<a>')
                    .attr('href', longUrl || '#')
                    .attr('target', '_blank')
                    .attr('rel', 'noopener')
                    .addClass('text-blue-600 underline break-all')
                    .text(longUrl);
                $longCell.append($longLink);
                $tr.append($longCell);

                // Short URL cell (with copy & open)
                const $shortCell = $('<td>').addClass('border p-3 align-top break-words max-w-[260px]');
                const $flex = $('<div>').addClass('flex items-center gap-2');
                const $span = $('<span>').addClass('truncate').text(shortUrl);
                const $copyBtn = $('<button>').addClass('copy-btn px-2 py-1 border rounded text-sm')
                    .attr('type', 'button')
                    .attr('data-copy', shortUrl)
                    .text('Copy');
                const $openA = $('<a>').addClass('px-2 py-1 bg-indigo-600 text-white rounded text-sm')
                    .attr('href', shortUrl)
                    .attr('target', '_blank')
                    .attr('rel', 'noopener')
                    .text('Open');
                $flex.append($span, $copyBtn, $openA);
                $shortCell.append($flex);
                $tr.append($shortCell);

                // Hits
                $tr.append($('<td>').addClass('border p-3 text-center align-top').text(row.hits ?? 0));

                // Created On
                const createdText = row.created_at ? formatDate(row.created_at) : '';
                $tr.append($('<td>').addClass('border p-3 align-top').text(createdText));

                // Created By
                const creator = (row.user && row.user.name) ? `${row.user.name} (${row.user.email})` : '—';
                $tr.append($('<td>').addClass('border p-3 align-top').text(creator));

                return $tr;
            }


            /* Copy handler (delegated) */
            $(document).on('click', '.copy-btn', function () {
                const text = $(this).attr('data-copy') || '';
                if (!navigator.clipboard) {
                    // fallback
                    const $temp = $('<textarea>').val(text).appendTo('body').select();
                    try { document.execCommand('copy'); showToast('Copied'); } catch { showToast('Copy failed'); }
                    $temp.remove();
                    return;
                }
                navigator.clipboard.writeText(text).then(() => showToast('Copied to clipboard'), () => showToast('Copy failed'));
            });

            /* Submit (create short URL) */
            $form.on("submit", function (e) {
                e.preventDefault();
                $error.hide();

                const longUrlVal = $longUrl.val().trim();
                if (!longUrlVal) {
                    $error.text("Please enter a valid URL").show();
                    return;
                }

                const $submit = $form.find("button[type='submit']");
                $submit.prop("disabled", true).text("Generating...");

                $.ajax({
                    url: ROUTES.createShort,
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": token,
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    data: { long_url: longUrlVal },
                    success: function (res) {
                        hideModal();
                        showToast('Short URL created');
                        // If API returns the created record, prepend it, otherwise refresh.

                    },
                    error: function (xhr) {
                        let msg = "Error generating short URL.";
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors)[0][0];
                        }
                        $error.text(msg).show();
                    },
                    complete: function () {
                        $submit.prop("disabled", false).text("Generate Short URL");
                    }
                });

            });


        });
    </script>
    <div
    x-data="{ openAdminInvite: false, showToast: false }"
    x-init="
        window.addEventListener('open-admin-invite', () => openAdminInvite = true);

        @if($errors->any())
            openAdminInvite = true;
        @endif

        @if(session('success'))
            showToast = true;
            setTimeout(() => showToast = false, 4000);
        @endif
    "
>
    <!-- Popup Overlay -->
    <div
        x-show="openAdminInvite"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center"
    >
        <div
            x-show="openAdminInvite"
            x-transition
            @click.away="openAdminInvite = false"
            class="bg-white w-full max-w-md mx-4 rounded-lg shadow-xl p-6"
        >
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Invite User to Company</h3>
                <button @click="openAdminInvite = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.invite.send') }}" class="mt-4">
                @csrf

                <!-- Select Role -->
                <div class="mb-3">
                    <label class="block text-sm font-medium">Select Role</label>
                    <select name="role" required class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Name -->
                <div class="mb-3">
                    <label class="block text-sm font-medium">User Name</label>
                    <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full border rounded px-3 py-2" />
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="block text-sm font-medium">User Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border rounded px-3 py-2" />
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="openAdminInvite = false" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Send</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div
        x-show="showToast"
        x-transition
        x-cloak
        class="fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow z-50"
    >
        {{ session('success') }}
    </div>
</div>

</x-app-layout>
