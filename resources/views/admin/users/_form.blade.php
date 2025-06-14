@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Left Column -->
    <div class="space-y-6">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required autofocus>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                {{ isset($user) ? 'New Password' : 'Password' }}
            </label>
            <input type="password" name="password" id="password"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   {{ !isset($user) ? 'required' : '' }}>
            @if(isset($user))
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
            @endif
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                {{ isset($user) ? 'Confirm New Password' : 'Confirm Password' }}
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   {{ !isset($user) ? 'required' : '' }}>
        </div>
    </div>


    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <div class="mt-2 space-y-2">
                <div class="flex items-center">
                    <input id="is_active_true" name="is_active" type="radio" value="1" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                           {{ old('is_active', isset($user) && $user->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                    <label for="is_active_true" class="ml-2 block text-sm text-gray-700">
                        Active
                    </label>
                </div>
                <div class="flex items-center">
                    <input id="is_active_false" name="is_active" type="radio" value="0"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                           {{ old('is_active', isset($user) && $user->is_active ? '1' : '0') == '0' ? 'checked' : '' }}>
                    <label for="is_active_false" class="ml-2 block text-sm text-gray-700">
                        Inactive
                    </label>
                </div>
            </div>
            @error('is_active')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Verification -->
        @if(isset($user) && !$user->hasVerifiedEmail())
        <div>
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="send_verification" name="send_verification" type="checkbox" 
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="send_verification" class="font-medium text-gray-700">Send email verification</label>
                    <p class="text-gray-500">Email the user a verification link</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Roles -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
            @error('roles')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            
            <div class="space-y-2">
                @foreach($roles as $role)
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="role-{{ $role->id }}" name="roles[]" type="checkbox" 
                                   value="{{ $role->id }}"
                                   @if(in_array($role->id, old('roles', $userRoles ?? []))) checked @endif
                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="role-{{ $role->id }}" class="font-medium text-gray-700">
                                {{ $role->name }}
                            </label>
                            @if($role->description)
                                <p class="text-gray-500 text-xs">{{ $role->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.users.index') }}" class="mr-4 text-sm font-medium text-gray-700 hover:text-gray-500">
        Cancel
    </a>
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        {{ isset($user) ? 'Update User' : 'Create User' }}
    </button>
</div>