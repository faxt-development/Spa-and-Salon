<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Staff') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p class="font-bold">{{ __('Validation Error') }}</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        @if(isset($prefill) && $prefill)
                            <input type="hidden" name="user_id" value="{{ $prefill['user_id'] }}">
                            <input type="hidden" name="is_admin" value="{{ $prefill['is_admin'] }}">
                            
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                                <p class="font-bold">{{ __('Adding yourself as a staff member') }}</p>
                                <p>{{ __('You are adding yourself as a staff member. Your account information has been pre-filled.') }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Personal Information') }}</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="first_name" :value="__('First Name')" :required="true" />
                                        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name') ?? (isset($prefill) ? $prefill['first_name'] : '')" required autofocus />
                                    </div>

                                    <div>
                                        <x-input-label for="last_name" :value="__('Last Name')" :required="true" />
                                        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name') ?? (isset($prefill) ? $prefill['last_name'] : '')" required />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="email" :value="__('Email')" :required="true" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email') ?? (isset($prefill) ? $prefill['email'] : '')" required />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="phone" :value="__('Phone')" :required="true" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="position" :value="__('Position')" :required="true" />
                                    <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position')" required />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="bio" :value="__('Bio')" />
                                    <textarea id="bio" name="bio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('bio') }}</textarea>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="profile_image" :value="__('Profile Image')" />
                                    <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full" accept="image/*" />
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Account Information') }}</h3>

                                @if(!isset($prefill) || !$prefill)
                                <div class="mt-4">
                                    <x-input-label for="password" :value="__('Password')" :required="true" />
                                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" :required="true" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                                </div>
                                @else
                                <div class="mt-4 bg-gray-100 p-3 rounded">
                                    <p class="text-sm text-gray-600">{{ __('Your existing account password will be used.') }}</p>
                                </div>
                                @endif

                                <div class="mt-4">
                                    <x-input-label for="role" :value="__('Role')" :required="true" />
                                    <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">{{ __('Select Role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                {{ old('role') == $role->id ? 'selected' : '' }}
                                                {{ (isset($prefill) && $prefill['is_admin'] && $role->name == 'admin') ? 'selected' : '' }}
                                                {{ (isset($prefill) && $prefill['is_admin'] && !old('role') && $role->name == 'staff') ? 'selected' : '' }}>
                                                {{ $role->display_name ?? $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(isset($prefill) && $prefill['is_admin'])
                                        <p class="mt-1 text-sm text-gray-600">{{ __('As an admin, you can choose to be added as staff or admin role.') }}</p>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <label for="active" class="inline-flex items-center">
                                        <input id="active" name="active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('active') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <label for="is_employee" class="inline-flex items-center">
                                        <input id="is_employee" name="is_employee" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('is_employee') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Is Employee') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Work Schedule -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Work Schedule') }}</h3>

                                <div class="mt-4">
                                    <x-input-label :value="__('Work Days')" />
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="work_days[]" value="{{ $day }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ is_array(old('work_days')) && in_array($day, old('work_days')) ? 'checked' : '' }}>
                                                <span class="ml-2 text-sm text-gray-600">{{ __(ucfirst($day)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <x-input-label for="work_start_time" :value="__('Start Time')" />
                                        <x-text-input id="work_start_time" name="work_start_time" type="time" class="mt-1 block w-full" :value="old('work_start_time')" />
                                    </div>

                                    <div>
                                        <x-input-label for="work_end_time" :value="__('End Time')" />
                                        <x-text-input id="work_end_time" name="work_end_time" type="time" class="mt-1 block w-full" :value="old('work_end_time')" />
                                    </div>
                                </div>
                            </div>

                            <!-- Compensation -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Compensation') }}</h3>



                                <!-- Employee section that appears when Is Employee is checked -->
                                <div id="employee-section" class="mt-4 p-4 bg-blue-50 rounded-lg" style="display: {{ old('is_employee') ? 'block' : 'none' }}">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">{{ __('Employee Information') }}</h4>
                                    
                                    <div class="mt-2">
                                        <x-input-label for="hourly_rate" :value="__('Hourly Rate')" :required="true" />
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <x-text-input id="hourly_rate" name="employee[hourly_rate]" type="number" step="0.01" min="0" class="pl-7 block w-full" :value="old('employee.hourly_rate')" />
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="commission_rate" :value="__('Commission Rate (%)')" />
                                    <x-text-input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('commission_rate')" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Qualifications & Notes') }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="specialties" :value="__('Specialties')" />
                                    <textarea id="specialties" name="specialties" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3" placeholder="Enter specialties, one per line">{{ old('specialties') }}</textarea>
                                </div>

                                <div>
                                    <x-input-label for="certifications" :value="__('Certifications')" />
                                    <textarea id="certifications" name="certifications" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3" placeholder="Enter certifications, one per line">{{ old('certifications') }}</textarea>
                                </div>

                                <div>
                                    <x-input-label for="languages" :value="__('Languages')" />
                                    <textarea id="languages" name="languages" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3" placeholder="Enter languages, one per line">{{ old('languages') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Create Staff Member') }}
                            </x-primary-button>
                        </div>

                        <!-- JavaScript to toggle employee section visibility -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const isEmployeeCheckbox = document.getElementById('is_employee');
                                const employeeSection = document.getElementById('employee-section');
                                
                                isEmployeeCheckbox.addEventListener('change', function() {
                                    employeeSection.style.display = this.checked ? 'block' : 'none';
                                });
                            });
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
