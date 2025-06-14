@csrf

<div class="space-y-6">
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Permission Name *</label>
        <div class="mt-1">
            <input type="text" name="name" id="name" 
                   value="{{ old('name', $permission->name ?? '') }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="e.g., view users" required>
        </div>
        <p class="mt-1 text-xs text-gray-500">Use lowercase with words separated by hyphens (e.g., edit-posts)</p>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Display Name -->
    <div>
        <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
        <div class="mt-1">
            <input type="text" name="display_name" id="display_name" 
                   value="{{ old('display_name', $permission->display_name ?? '') }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="e.g., View Users">
        </div>
        <p class="mt-1 text-xs text-gray-500">A human-readable name for the permission</p>
        @error('display_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <div class="mt-1">
            <textarea name="description" id="description" rows="3"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      placeholder="A brief description of what this permission allows">{{ old('description', $permission->description ?? '') }}</textarea>
        </div>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Guard Name -->
    <div>
        <label for="guard_name" class="block text-sm font-medium text-gray-700">Guard Name *</label>
        <div class="mt-1">
            <select name="guard_name" id="guard_name" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required>
                @foreach($guards as $guard)
                    <option value="{{ $guard }}" {{ (old('guard_name', $permission->guard_name ?? 'web') === $guard) ? 'selected' : '' }}>
                        {{ ucfirst($guard) }}
                    </option>
                @endforeach
            </select>
        </div>
        <p class="mt-1 text-xs text-gray-500">The guard that the permission belongs to</p>
        @error('guard_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Roles -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Roles</label>
        @error('roles')
            <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        
        <div class="bg-white rounded-md shadow-sm -space-y-px">
            @forelse($roles as $role)
                <div class="border-b border-gray-200 p-4 flex items-start">
                    <div class="flex items-center h-5">
                        <input id="role-{{ $role->id }}" name="roles[]" type="checkbox" 
                               value="{{ $role->id }}"
                               @if(in_array($role->id, old('roles', $permissionRoles ?? []))) checked @endif
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="role-{{ $role->id }}" class="block text-sm font-medium text-gray-700">
                            {{ $role->name }}
                        </label>
                        @if($role->description)
                            <p class="text-xs text-gray-500">{{ $role->description }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-sm text-gray-500">
                    No roles found. Please create roles first.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-8">
        <a href="{{ route('admin.permissions.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
        </a>
        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ isset($permission) ? 'Update Permission' : 'Create Permission' }}
        </button>
    </div>
</div>