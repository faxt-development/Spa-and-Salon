<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 border-b border-gray-200 sm:px-6 flex justify-between items-center">
        <h3 class="text-lg font-medium leading-6 text-gray-900">New Admin Onboarding</h3>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-blue-800">
            New
        </span>
    </div>
    <div class="p-6">
        <div class="text-sm text-gray-600 mb-4">
            Welcome to Faxtina! Complete these essential steps to get started with managing your spa and salon business.
        </div>
        <div class="space-y-3">
            <div class="flex items-start">
                <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="{{ url('/profile') }}" class="ml-3 text-sm text-blue-600 hover:text-blue-800 hover:underline">Complete your profile setup</a>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="{{ route('admin.company.edit') }}" class="ml-3 text-sm text-blue-600 hover:text-blue-800 hover:underline">Configure business settings</a>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                    @php
                        $user = auth()->user();
                        $company = $user->primaryCompany();
                        $hasStaff = false;

                        if ($company) {
                            // Get user IDs associated with this company
                            $userIds = $company->users()->pluck('users.id');
                            // Check if there are any staff records for these users
                            $hasStaff = App\Models\Staff::whereIn('user_id', $userIds)->exists();
                        }
                    @endphp
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $hasStaff ? 'text-blue-500' : 'text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="{{ route('admin.staff.index') }}" class="ml-3 text-sm text-blue-600 hover:text-blue-800 hover:underline">Add staff members</a>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 h-5 w-5 relative mt-1">
                    @php
                        $hasServices = $company ? $company->services()->exists() : false;
                    @endphp
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $hasServices ? 'text-blue-500' : 'text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="{{ route('admin.services') }}" class="ml-3 text-sm text-blue-600 hover:text-blue-800 hover:underline">Set up services</a>
            </div>
        </div>
        <div class="mt-5">
            <a href="{{ route('admin.onboarding-checklist') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 hover:text-white focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                View Full Checklist
            </a>
        </div>
    </div>
</div>
