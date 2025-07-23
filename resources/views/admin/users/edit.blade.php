<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User') }}: {{ $user->name }}
            </h2>
            <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-gray-600 hover:text-gray-900">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Edit User: {{ $user->name }}</h2>
                        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Back to User
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-700">
                                        There were some errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-600">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Role-specific Edit Links -->
                    @if($user->roles->isNotEmpty())
                        <div class="mb-6 bg-primary-50 border-l-4 border-blue-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-700">Role-specific Information</h3>
                                    <div class="mt-2 text-sm text-blue-600">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @if($user->hasRole('staff') && $user->staff)
                                                <li>
                                                    <a href="{{ route('admin.staff.edit', $user->staff->id) }}" class="text-blue-600 hover:underline">
                                                        Edit Staff Profile
                                                    </a>
                                                </li>
                                            @endif
                                            @if($user->hasRole('employee') && $user->employee)
                                                <li>
                                                    <a href="{{ route('admin.employees.edit', $user->employee->id) }}" class="text-blue-600 hover:underline">
                                                        Edit Employee Profile
                                                    </a>
                                                </li>
                                            @endif
                                            @if($user->hasRole('admin'))
                                                <li>
                                                    Admin users have full access to the system.
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @method('PUT')
                        @include('admin.users._form', [
                            'user' => $user,
                            'userRoles' => $user->roles->pluck('id')->toArray()
                        ])
                    </form>

                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-red-600">Danger Zone</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Once you delete a user, there is no going back. Please be certain.</p>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
