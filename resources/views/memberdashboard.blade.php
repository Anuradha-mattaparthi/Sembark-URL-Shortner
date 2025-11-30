{{-- member dashboard --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Welcome Member!</h2>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="py-12">
        <div class="bg-white p-6 rounded shadow max-w-7xl mx-auto">

            <div class="flex justify-end mb-4">
                <button id="openModalBtn"
                    class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">
                    Generate URL
                </button>
            </div>

            <h3 class="text-lg font-semibold mb-3">Generated URLs</h3>

            <table class="w-full border-collapse border border-gray-300" id="shortUrlTable">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">Long URL</th>
                        <th class="border p-2">Short URL</th>
                        <th class="border p-2">Hits</th>
                        <th class="border p-2">Created On</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>
    </div>

    <!-- Modal -->
    <div id="shortUrlModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

        <div class="bg-white rounded-lg w-full max-w-lg p-6 shadow relative">

            <button id="closeModalBtn" class="absolute top-2 right-3 text-gray-600 text-2xl">&times;</button>

            <h2 class="text-lg font-semibold mb-4">Generate Short URL</h2>

            <form id="shortUrlForm">
                @csrf
                <label class="block text-sm font-medium">Long URL</label>
                <input type="url" id="long_url" required class="mt-1 w-full border rounded p-2"
                       placeholder="https://example.com/page">

                <p id="error" class="text-red-600 text-sm mt-2 hidden"></p>

                <button type="submit"
                    class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded">
                    Generate Short URL
                </button>
            </form>

        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- AJAX LOGIC -->
    <script>
    function formatDate(dt) {
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

        $(function () {

            const token = $('meta[name="csrf-token"]').attr("content");

            const $modal = $("#shortUrlModal");
            const $openBtn = $("#openModalBtn");
            const $closeBtn = $("#closeModalBtn");
            const $form = $("#shortUrlForm");
            const $longUrl = $("#long_url");
            const $error = $("#error");

            /* -----------------------
                MODAL FUNCTIONS
            ----------------------- */
            function showModal() {
                $modal.removeClass("hidden").addClass("flex");
                $error.hide().text("");
                $longUrl.val("").focus();
            }

            function hideModal() {
                $modal.addClass("hidden").removeClass("flex");
            }

            $openBtn.on("click", showModal);
            $closeBtn.on("click", hideModal);
            $modal.on("click", (e) => { if (e.target === $modal[0]) hideModal(); });


            /* -----------------------
                LOAD ALL ROWS FROM DB
            ----------------------- */
            function loadAllRows() {
                $.ajax({
                    url: "{{ route('memberdashboard') }}",
                    type: "GET",
                    dataType: "json",
                    headers: { "X-Requested-With": "XMLHttpRequest" },

                    success: function(rows) {
                        const tbody = $("#shortUrlTable tbody");
                        tbody.empty();

                        const user = "{{ auth()->user()->name }} ({{ auth()->user()->email }})";

                        rows.forEach(row => {
                            const shortUrl = "{{ env('SHORT_DOMAIN', config('app.url')) }}/r/" + row.short_code;

                            tbody.append(`
                                <tr>


                                    <td class="border p-2">
                                        <a href="${row.long_url}" target="_blank" class="text-blue-600 underline">
                                            ${row.long_url}
                                        </a>
                                    </td>

                                    <td class="border p-2">
                                       ${shortUrl}
                                        <button onclick="navigator.clipboard.writeText('${shortUrl}')" class="ml-1 px-2 py-1 border rounded">Copy</button>
                                        <a href="${shortUrl}" target="_blank" class="ml-1 px-2 py-1 bg-indigo-600 text-white rounded">Open</a>
                                    </td>

                                    <td class="border p-2 text-center">${row.hits}</td>
                                   <td class="border p-2">${formatDate(row.created_at).replace(',', '')}</td>

                                </tr>
                            `);
                        });
                    }
                });
            }



            loadAllRows();



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
                    url: "{{ route('short-urls.store') }}",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": token,
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    data: { long_url: longUrlVal },

                    success: function () {
                        hideModal();
                        loadAllRows();
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

</x-app-layout>
