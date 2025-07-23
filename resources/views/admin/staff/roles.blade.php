<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roles & Permissions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Roles List -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Roles') }}</h3>
                                <button type="button" onclick="document.getElementById('add-role-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Add New Role') }}
                                </button>
                            </div>

                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach ($roles as $role)
                                        <li>
                                            <div class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium text-indigo-600 truncate">
                                                            {{ $role->display_name ?? $role->name }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            {{ $role->description ?? __('No description provided') }}
                                                        </p>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button type="button" onclick="editRole('{{ $role->id }}', '{{ $role->name }}', '{{ $role->display_name }}', '{{ $role->description }}', {{ json_encode($role->permissions->pluck('id')) }})" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            {{ __('Edit') }}
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <p class="text-xs font-medium text-gray-500 mb-1">{{ __('Permissions:') }}</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        @forelse ($role->permissions as $permission)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                {{ $permission->name }}
                                                            </span>
                                                        @empty
                                                            <span class="text-xs text-gray-500">{{ __('No permissions assigned') }}</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Permissions List -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Permissions') }}</h3>
                            </div>

                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach ($permissions as $permission)
                                            <div class="bg-gray-50 p-2 rounded">
                                                <p class="text-sm font-medium text-gray-900">{{ $permission->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $permission->guard_name }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div id="add-role-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ __('Add New Role') }}
                        </h3>
                        <div class="mt-4">
                            <form id="add-role-form" action="{{ route('admin.staff.roles.store') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <x-input-label for="name" :value="__('Role Name')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('This is the internal name used by the system. Use lowercase and underscores.') }}</p>
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="display_name" :value="__('Display Name')" />
                                    <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full" required />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="2"></textarea>
                                </div>

                                <div class="mb-4">
                                    <x-input-label :value="__('Permissions')" />
                                    <div class="mt-2 h-48 overflow-y-auto p-2 border rounded-md">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach ($permissions as $permission)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-600">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('add-role-form').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="document.getElementById('add-role-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="edit-role-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ __('Edit Role') }}
                        </h3>
                        <div class="mt-4">
                            <form id="edit-role-form" action="" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-4">
                                    <x-input-label for="edit_name" :value="__('Role Name')" />
                                    <x-text-input id="edit_name" type="text" class="mt-1 block w-full bg-gray-100" disabled />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="edit_display_name" :value="__('Display Name')" />
                                    <x-text-input id="edit_display_name" name="display_name" type="text" class="mt-1 block w-full" required />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="edit_description" :value="__('Description')" />
                                    <textarea id="edit_description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="2"></textarea>
                                </div>

                                <div class="mb-4">
                                    <x-input-label :value="__('Permissions')" />
                                    <div class="mt-2 h-48 overflow-y-auto p-2 border rounded-md">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2" id="edit-permissions-container">
                                            @foreach ($permissions as $permission)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="edit-permission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-600">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('edit-role-form').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="document.getElementById('edit-role-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        function editRole(id, name, displayName, description, permissions) {
            const form = document.getElementById('edit-role-form');
            form.action = "{{ route('admin.staff.roles.update', '') }}/" + id;

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_display_name').value = displayName || name;
            document.getElementById('edit_description').value = description || '';

            // Reset all checkboxes
            const checkboxes = document.querySelectorAll('.edit-permission-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check the appropriate permissions
            permissions.forEach(permissionId => {
                const checkbox = document.querySelector(`.edit-permission-checkbox[value="${permissionId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            document.getElementById('edit-role-modal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
