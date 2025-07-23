<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.staff.edit', $staff->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Staff Profile -->
                        <div class="md:col-span-1">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex flex-col items-center">
                                    @if ($staff->profile_image)
                                        <img src="{{ asset('storage/' . $staff->profile_image) }}" alt="{{ $staff->full_name }}" class="h-32 w-32 object-cover rounded-full mb-4">
                                    @else
                                        <div class="h-32 w-32 rounded-full bg-gray-300 flex items-center justify-center mb-4">
                                            <span class="text-gray-600 text-2xl font-medium">{{ substr($staff->first_name, 0, 1) . substr($staff->last_name, 0, 1) }}</span>
                                        </div>
                                    @endif

                                    <h3 class="text-xl font-bold text-gray-900">{{ $staff->full_name }}</h3>
                                    <p class="text-gray-600">{{ $staff->position }}</p>

                                    <div class="mt-2">
                                        @if ($staff->active)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 border-t border-gray-200 pt-4">
                                    <div class="grid grid-cols-1 gap-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">{{ __('Email') }}:</span>
                                            <a href="mailto:{{ $staff->email }}" class="text-blue-600 hover:underline">{{ $staff->email }}</a>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">{{ __('Phone') }}:</span>
                                            <a href="tel:{{ $staff->phone }}" class="text-blue-600 hover:underline">{{ $staff->phone }}</a>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">{{ __('Role') }}:</span>
                                            <span>
                                                @if ($staff->user && $staff->user->roles->isNotEmpty())
                                                    {{ $staff->user->roles->first()->name }}
                                                @else
                                                    {{ __('No Role Assigned') }}
                                                @endif
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Qualifications -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Qualifications') }}</h4>

                                @if ($staff->specialties && count($staff->specialties) > 0)
                                    <div class="mb-3">
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Specialties') }}</h5>
                                        <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                            @foreach ($staff->specialties as $specialty)
                                                <li>{{ $specialty }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($staff->certifications && count($staff->certifications) > 0)
                                    <div class="mb-3">
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Certifications') }}</h5>
                                        <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                            @foreach ($staff->certifications as $certification)
                                                <li>{{ $certification }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($staff->languages && count($staff->languages) > 0)
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Languages') }}</h5>
                                        <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                            @foreach ($staff->languages as $language)
                                                <li>{{ $language }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Staff Details -->
                        <div class="md:col-span-2">
                            <!-- Bio -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Bio') }}</h4>
                                <p class="text-gray-600">{{ $staff->bio ?? __('No bio provided.') }}</p>
                            </div>

                            <!-- Work Schedule -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Work Schedule') }}</h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Work Days') }}</h5>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if ($staff->work_days && count($staff->work_days) > 0)
                                                @foreach ($staff->work_days as $day)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-primary-100 text-blue-800">
                                                        {{ __(ucfirst($day)) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-500">{{ __('No work days set') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Work Hours') }}</h5>
                                        <p class="mt-1 text-gray-600">
                                            @if ($staff->work_start_time && $staff->work_end_time)
                                                {{ $staff->work_start_time->format('g:i A') }} - {{ $staff->work_end_time->format('g:i A') }}
                                            @else
                                                {{ __('No work hours set') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Compensation -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Compensation') }}</h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Hourly Rate') }}</h5>
                                        <p class="mt-1 text-gray-600">
                                            @if ($staff->hourly_rate)
                                                ${{ number_format($staff->hourly_rate, 2) }}
                                            @else
                                                {{ __('Not set') }}
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <h5 class="text-sm font-medium text-gray-700">{{ __('Commission Rate') }}</h5>
                                        <p class="mt-1 text-gray-600">
                                            @if ($staff->commission_rate)
                                                {{ number_format($staff->commission_rate, 2) }}%
                                            @else
                                                {{ __('Not set') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Services -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Services') }}</h4>

                                @if ($staff->services->isNotEmpty())
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Service') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Primary') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($staff->services as $service)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->name }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            @if ($service->pivot->duration_override)
                                                                {{ $service->pivot->duration_override }} {{ __('min') }}
                                                            @else
                                                                {{ $service->duration }} {{ __('min') }}
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            @if ($service->pivot->price_override)
                                                                ${{ number_format($service->pivot->price_override, 2) }}
                                                            @else
                                                                ${{ number_format($service->price, 2) }}
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            @if ($service->pivot->is_primary)
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Yes') }}</span>
                                                            @else
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('No') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-gray-500">{{ __('No services assigned to this staff member.') }}</p>
                                @endif
                            </div>

                            <!-- Upcoming Appointments -->
                            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('Upcoming Appointments') }}</h4>

                                @if ($staff->appointments->where('start_time', '>=', now())->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date & Time') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Service') }}</th>
                                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($staff->appointments->where('start_time', '>=', now())->sortBy('start_time')->take(5) as $appointment)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {{ $appointment->start_time->format('M d, Y g:i A') }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            @if ($appointment->client)
                                                                {{ $appointment->client->first_name }} {{ $appointment->client->last_name }}
                                                            @else
                                                                {{ __('N/A') }}
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            @if ($appointment->service)
                                                                {{ $appointment->service->name }}
                                                            @else
                                                                {{ __('N/A') }}
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                            @switch($appointment->status)
                                                                @case('confirmed')
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                        {{ __('Confirmed') }}
                                                                    </span>
                                                                    @break
                                                                @case('pending')
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                                        {{ __('Pending') }}
                                                                    </span>
                                                                    @break
                                                                @case('cancelled')
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                        {{ __('Cancelled') }}
                                                                    </span>
                                                                    @break
                                                                @default
                                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                        {{ $appointment->status }}
                                                                    </span>
                                                            @endswitch
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-gray-500">{{ __('No upcoming appointments.') }}</p>
                                @endif
                            </div>

                            <!-- Notes -->
                            @if ($staff->notes)
                                <div class="bg-gray-50 p-4 rounded-lg mt-4">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('Notes') }}</h4>
                                    <p class="text-gray-600">{{ $staff->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
