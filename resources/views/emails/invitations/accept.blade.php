{{-- when Accepting the invite through emails  from admin and super admin dashboard--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Accept Invitation
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                @if(session('error'))
                    <div class="text-red-600 mb-4">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('invitations.accept.post', $invite->token) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Company</label>
                        <input type="text" value="{{ $invite->company->name ?? '-' }}" disabled
                               class="mt-1 block w-full border-gray-300 rounded-md bg-gray-50 p-2" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" type="text" value="{{ old('name', $invite->name) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md p-2" required />
                        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ $invite->email }}" readonly
                               class="mt-1 block w-full border-gray-300 rounded-md bg-gray-100 p-2" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="password" required
                               class="mt-1 block w-full border-gray-300 rounded-md p-2" />
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input name="password_confirmation" type="password" required
                               class="mt-1 block w-full border-gray-300 rounded-md p-2" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded">Accept Invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
