<!-- HQ ADMIN NEED ACCESS DATA FROM ALL EMPLOYEES ACROSS BRANCHES -->


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('HQ Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">Welcome, HQ Admin!</h3>
                    <p>You have access to all {{ $branches->count() }} branches:</p>
                    <ul class="mt-4">
                        @foreach($branches as $branch)
                            <li class="mb-2">
                                <strong>{{ $branch->name }}</strong> - {{ $branch->address }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>