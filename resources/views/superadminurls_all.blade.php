{{-- Super Admin all urls when clicking view more on Super admin dashboard --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                All Generated URLs
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded shadow">

                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold">All Generated URLs</h3>

                    <a href="{{ url()->previous() ?? route('dashboard') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded shadow-sm">
                        ← Back
                    </a>
                </div>

                <div class="overflow-x-auto border rounded">
                    <table class="w-full min-w-[900px] border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-3 text-left">Company</th>
                                <th class="border p-3 text-left">Long URL</th>
                                <th class="border p-3 text-left">Short URL</th>
                                <th class="border p-3 text-center">Hits</th>
                                <th class="border p-3 text-left">Created On</th>
                                <th class="border p-3 text-left">Created By</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($allUrls as $row)
                                <tr class="even:bg-white odd:bg-gray-50">
                                    {{-- Company --}}
                                    <td class="border p-3 align-top">
                                        {{ $row->company->name ?? '—' }}
                                    </td>

                                    {{-- Long URL --}}
                                    <td class="border p-3 max-w-[420px] break-words">
                                        <a href="{{ $row->long_url }}" target="_blank" rel="noopener"
                                           class="text-blue-600 underline break-all">
                                            {{ $row->long_url }}
                                        </a>
                                    </td>

                                    {{-- Short URL --}}
                                    <td class="border p-3 max-w-[260px] break-words">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate">{{ $shortDomain }}/r/{{ $row->short_code }}</span>

                                            <button data-copy="{{ $shortDomain }}/r/{{ $row->short_code }}"
                                                    class="px-2 py-1 border rounded text-sm">
                                                Copy
                                            </button>

                                            <a href="{{ $shortDomain }}/r/{{ $row->short_code }}" target="_blank"
                                               class="px-2 py-1 bg-indigo-600 text-white rounded text-sm">
                                                Open
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Hits --}}
                                    <td class="border p-3 text-center">{{ $row->hits }}</td>

                                    {{-- Created On --}}
                                    <td class="border p-3">
                                        {{ $row->created_at->format('d M Y h:i A') }}
                                    </td>

                                    {{-- Created By --}}
                                    <td class="border p-3">
                                        {{ $row->user->name ?? '—' }}
                                        <div class="text-xs text-gray-500">
                                            ({{ $row->user->email ?? '—' }})
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">No URLs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    {{-- Toast container (top-right) --}}
    <div id="toast" class="fixed top-6 right-6 z-60 hidden"></div>

    <script>
        // small toast helper
        function showToast(message, timeout = 2500) {
            const t = document.getElementById('toast');
            t.textContent = message;
            t.classList.remove('hidden');
            t.classList.add('block', 'bg-black/80', 'text-white', 'px-4', 'py-2', 'rounded');
            setTimeout(() => { t.classList.add('hidden'); t.classList.remove('block'); }, timeout);
        }

        // copy button handler (delegated)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-copy]');
            if (!btn) return;
            const text = btn.getAttribute('data-copy');
            if (!text) return;
            navigator.clipboard.writeText(text)
                .then(() => showToast('Copied to clipboard'))
                .catch(() => showToast('Copy failed'));
        });
    </script>
</x-app-layout>
