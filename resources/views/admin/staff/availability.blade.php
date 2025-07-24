@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Staff Availability Management</h1>
        <a href="{{ route('admin.staff.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
            Back to Staff List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <p class="text-gray-600 mb-4">
            Manage your staff availability schedule. Set working days and hours for each staff member to ensure proper scheduling of appointments.
        </p>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="lg:w-1/3">
                <h2 class="text-xl font-semibold mb-4">Staff Members</h2>
                <div class="space-y-4">
                    @foreach($staff as $staffMember)
                    <div class="staff-card p-4 border rounded-lg hover:bg-primary-50 cursor-pointer transition-colors"
                         data-staff-id="{{ $staffMember->id }}">
                        <div class="flex items-center">
                            @if($staffMember->profile_image)
                            <img src="{{ Storage::url($staffMember->profile_image) }}" alt="{{ $staffMember->full_name }}"
                                 class="w-12 h-12 rounded-full object-cover mr-4">
                            @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                                <span class="text-gray-600 text-lg font-bold">{{ substr($staffMember->first_name, 0, 1) }}{{ substr($staffMember->last_name, 0, 1) }}</span>
                            </div>
                            @endif
                            <div>
                                <h3 class="font-medium">{{ $staffMember->full_name }}</h3>
                                <p class="text-sm text-gray-500">{{ $staffMember->position }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:w-2/3">
                <h2 class="text-xl font-semibold mb-4">Weekly Schedule</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time/Day
                                </th>
                                @foreach($dateRange as $date)
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $date['day'] }}
                                    <div class="text-xs font-normal normal-case">{{ $date['date'] }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="availability-grid">
                            <tr>
                                <td colspan="{{ count($dateRange) + 1 }}" class="py-8 text-center text-gray-500">
                                    Select a staff member to view and edit their availability
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="success-alert" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">Availability has been updated successfully.</span>
                </div>

                <div id="error-alert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">An error occurred while saving.</span>
                </div>

                <div id="staff-availability-form" class="hidden mt-6 p-4 border rounded-lg">
                    <h3 class="text-lg font-medium mb-4">Edit Availability</h3>
                    <form id="availability-form" method="POST" action="{{ route('admin.staff.update-availability') }}">
                        @csrf
                        <input type="hidden" name="staff_id" id="form-staff-id">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Working Days</label>
                                <div class="space-y-2">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="work_days[]" value="{{ $day }}" id="day-{{ $day }}"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="day-{{ $day }}" class="ml-2 block text-sm text-gray-700 capitalize">
                                            {{ $day }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label for="work_start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                    <input type="time" name="work_start_time" id="work_start_time"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="work_end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                    <input type="time" name="work_end_time" id="work_end_time"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded">
                                Save Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Staff data
        const staffData = @json($staff);

        // Handle staff selection
        const staffCards = document.querySelectorAll('.staff-card');
        staffCards.forEach(card => {
            card.addEventListener('click', function() {
                const staffId = this.dataset.staffId;
                loadStaffAvailability(staffId);

                // Highlight selected staff
                staffCards.forEach(c => c.classList.remove('bg-primary-100', 'border-blue-500'));
                this.classList.add('bg-primary-100', 'border-blue-500');

                // Show the form
                document.getElementById('staff-availability-form').classList.remove('hidden');
            });
        });

        function loadStaffAvailability(staffId) {
            // Find the staff member in the data
            const staffMember = staffData.find(s => s.id == staffId);
            if (!staffMember) {
                console.error('Staff member not found in data');
                return;
            }

            // Send log to server
            fetch('{{ route('admin.staff.log-activity') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    staff_id: staffId,
                    activity_type: 'view_availability',
                    details: 'Viewed staff availability',
                    url: window.location.href
                })
            }).catch(error => console.error('Error logging activity:', error));

            // Set form values
            document.getElementById('form-staff-id').value = staffId;

            // Reset checkboxes
            document.querySelectorAll('input[name="work_days[]"]').forEach(cb => cb.checked = false);

            // Set working days
            if (staffMember.work_days) {
                staffMember.work_days.forEach(day => {
                    const checkbox = document.getElementById(`day-${day}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    } else {
                        console.warn(`Checkbox not found for day-${day}`);
                    }
                });
            } else {
                console.warn('No work days defined for staff member');
            }

            // Set working hours - default to 9:00 AM to 5:00 PM if not set
            if (staffMember.work_start_time) {
                // Parse the time correctly from the database format
                let localHours, localMinutes;

                if (staffMember.work_start_time.includes('T')) {
                    // Handle ISO format like "2023-01-01T09:00:00.000Z"
                    // Parse UTC time and convert to local time
                    const utcDate = new Date(staffMember.work_start_time);
                    localHours = utcDate.getHours();
                    localMinutes = utcDate.getMinutes();
                } else {
                    // Handle time-only format like "09:00:00"
                    [localHours, localMinutes] = staffMember.work_start_time.split(':').map(Number);
                }

                const formattedStartTime = `${String(localHours).padStart(2, '0')}:${String(localMinutes).padStart(2, '0')}`;
                document.getElementById('work_start_time').value = formattedStartTime;
            } else {
                document.getElementById('work_start_time').value = '09:00';
            }

            if (staffMember.work_end_time) {
                // Parse the time correctly from the database format
                let localHours, localMinutes;

                if (staffMember.work_end_time.includes('T')) {
                    // Handle ISO format like "2023-01-01T17:00:00.000Z"
                    // Parse UTC time and convert to local time
                    const utcDate = new Date(staffMember.work_end_time);
                    localHours = utcDate.getHours();
                    localMinutes = utcDate.getMinutes();
                } else {
                    // Handle time-only format like "17:00:00"
                    [localHours, localMinutes] = staffMember.work_end_time.split(':').map(Number);
                }

                const formattedEndTime = `${String(localHours).padStart(2, '0')}:${String(localMinutes).padStart(2, '0')}`;
                document.getElementById('work_end_time').value = formattedEndTime;
            } else {
                document.getElementById('work_end_time').value = '17:00';
            }

            // Update the availability grid
            updateAvailabilityGrid(staffMember);
        }

        function updateAvailabilityGrid(staffMember) {
            const grid = document.getElementById('availability-grid');
            const dateRange = @json($dateRange);
            const businessHours = @json($businessHours);

            grid.innerHTML = '';

            // Helper function to convert UTC time to local time for display
            const parseTime = timeStr => {
    if (!timeStr) {
        console.warn('Empty time string, returning [0, 0]');
        return [0, 0];
    }

    let hours, minutes;

    if (timeStr.includes('T')) {
        [hours, minutes] = timeStr.split('T')[1].split(':').map(Number);
    } else {
        [hours, minutes] = timeStr.split(':').map(str => parseInt(str, 10));
    }

    const baseDate = new Date();
    const utcDate = new Date(Date.UTC(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate(), hours, minutes, 0));
    const localHours = utcDate.getHours();
    const localMinutes = utcDate.getMinutes();

    return [localHours, localMinutes];
};

            let earliestOpenHour = 23;
            let latestCloseHour = 0;

            Object.values(businessHours).forEach(({ is_closed, open_time, close_time }) => {
                if (!is_closed) {
                    const [openHour] = open_time.split(':').map(Number);
                    const [closeHour] = close_time.split(':').map(Number);
                    earliestOpenHour = Math.min(earliestOpenHour, openHour);
                    latestCloseHour = Math.max(latestCloseHour, closeHour);
                }
            });

            earliestOpenHour = earliestOpenHour === 23 ? 9 : earliestOpenHour;
            latestCloseHour = latestCloseHour === 0 ? 17 : latestCloseHour;

            const timeSlots = [];
            for (let hour = earliestOpenHour; hour <= latestCloseHour; hour++) {
                timeSlots.push(`${hour}:00`);
                if (hour < latestCloseHour) {
                    timeSlots.push(`${hour}:30`);
                }
            }

            timeSlots.forEach(timeSlot => {
                const row = document.createElement('tr');

                const timeCell = document.createElement('td');
                timeCell.className = 'py-2 px-4 border-b border-gray-200';

                const [hour, minute] = timeSlot.split(':').map(Number);
                const displayHour = hour % 12 || 12;
                const ampm = hour >= 12 ? 'PM' : 'AM';
                timeCell.textContent = `${displayHour}:${String(minute).padStart(2, '0')} ${ampm}`;
                row.appendChild(timeCell);

                const [startHour, startMinute] = parseTime(staffMember.work_start_time);
                const [endHour, endMinute] = parseTime(staffMember.work_end_time);
                const workMinutesStart = startHour * 60 + startMinute;
                const workMinutesEnd = endHour * 60 + endMinute;
                const slotMinutes = hour * 60 + minute;

                dateRange.forEach(({ full_day }) => {
                    const dayCell = document.createElement('td');
                    dayCell.className = 'py-2 px-4 border-b border-gray-200 text-center';

                    const isWorkDay = Array.isArray(staffMember.work_days) &&
                        staffMember.work_days.includes(full_day.toLowerCase());

                    const isAvailable = isWorkDay &&
                        slotMinutes >= workMinutesStart &&
                        slotMinutes < workMinutesEnd;

                    if (isAvailable) {
                        dayCell.classList.add('bg-green-100');
                        dayCell.innerHTML = '<span class="text-green-600">Available</span>';
                    } else {
                        dayCell.classList.add('bg-gray-100');
                        dayCell.innerHTML = '<span class="text-gray-400">Off</span>';
                    }

                    row.appendChild(dayCell);
                });

                grid.appendChild(row);
            });
        }

        // Handle form submission
        document.getElementById('availability-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Convert to JSON
            const jsonData = {};
            formData.forEach((value, key) => {
                if (jsonData[key]) {
                    if (!Array.isArray(jsonData[key])) {
                        jsonData[key] = [jsonData[key]];
                    }
                    jsonData[key].push(value);
                } else {
                    jsonData[key] = value;
                }
            });

            // Convert local time to UTC for storage
            if (jsonData.work_start_time) {
                const [hours, minutes] = jsonData.work_start_time.split(':').map(Number);

                // Get current date in local time
                const now = new Date();

                // Create a date object with the input time in local timezone
                const localDate = new Date(
                    now.getFullYear(),
                    now.getMonth(),
                    now.getDate(),
                    hours,
                    minutes,
                    0
                );

                // Calculate the UTC time
                const utcHours = localDate.getUTCHours();
                const utcMinutes = localDate.getUTCMinutes();

                // Create UTC time string
                jsonData.work_start_time = `${String(utcHours).padStart(2, '0')}:${String(utcMinutes).padStart(2, '0')}`;
            }

            if (jsonData.work_end_time) {
                const [hours, minutes] = jsonData.work_end_time.split(':').map(Number);

                // Get current date in local time
                const now = new Date();

                // Create a date object with the input time in local timezone
                const localDate = new Date(
                    now.getFullYear(),
                    now.getMonth(),
                    now.getDate(),
                    hours,
                    minutes,
                    0
                );

                // Calculate the UTC time
                const utcHours = localDate.getUTCHours();
                const utcMinutes = localDate.getUTCMinutes();

                // Create UTC time string
                jsonData.work_end_time = `${String(utcHours).padStart(2, '0')}:${String(utcMinutes).padStart(2, '0')}`;
            }

            // Make sure work_days is always an array
            if (jsonData['work_days[]']) {
                jsonData.work_days = Array.isArray(jsonData['work_days[]']) ? jsonData['work_days[]'] : [jsonData['work_days[]']];
                delete jsonData['work_days[]'];
            } else {
                jsonData.work_days = [];
            }

            // Send the data
            fetch('{{ route('admin.staff.update-availability') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successAlert = document.getElementById('success-alert');
                    successAlert.classList.remove('hidden');
                    setTimeout(() => {
                        successAlert.classList.add('hidden');
                    }, 3000);

                    // Update the staff data
                    const staffIndex = staffData.findIndex(s => s.id == jsonData.staff_id);
                    if (staffIndex !== -1) {
                        staffData[staffIndex].work_days = jsonData.work_days || [];
                        staffData[staffIndex].work_start_time = jsonData.work_start_time;
                        staffData[staffIndex].work_end_time = jsonData.work_end_time;

                        // Update the grid
                        updateAvailabilityGrid(staffData[staffIndex]);
                    }
                } else {
                    // Show error message
                    const errorAlert = document.getElementById('error-alert');
                    errorAlert.textContent = data.message || 'An error occurred';
                    errorAlert.classList.remove('hidden');
                    setTimeout(() => {
                        errorAlert.classList.add('hidden');
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const errorAlert = document.getElementById('error-alert');
                errorAlert.textContent = 'An error occurred while saving';
                errorAlert.classList.remove('hidden');
                setTimeout(() => {
                    errorAlert.classList.add('hidden');
                }, 3000);
            });
        });
    });
</script>
@endpush
@endsection
