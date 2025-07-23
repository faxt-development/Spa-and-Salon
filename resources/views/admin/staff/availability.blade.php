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
            if (!staffMember) return;

            // Set form values
            document.getElementById('form-staff-id').value = staffId;

            // Reset checkboxes
            document.querySelectorAll('input[name="work_days[]"]').forEach(cb => cb.checked = false);

            // Set working days
            if (staffMember.work_days) {
                staffMember.work_days.forEach(day => {
                    const checkbox = document.getElementById(`day-${day}`);
                    if (checkbox) checkbox.checked = true;
                });
            }

            // Set working hours
            if (staffMember.work_start_time) {
                const startTime = new Date(staffMember.work_start_time);
                document.getElementById('work_start_time').value =
                    `${String(startTime.getHours()).padStart(2, '0')}:${String(startTime.getMinutes()).padStart(2, '0')}`;
            }

            if (staffMember.work_end_time) {
                const endTime = new Date(staffMember.work_end_time);
                document.getElementById('work_end_time').value =
                    `${String(endTime.getHours()).padStart(2, '0')}:${String(endTime.getMinutes()).padStart(2, '0')}`;
            }

            // Update the availability grid
            updateAvailabilityGrid(staffMember);
        }

        function updateAvailabilityGrid(staffMember) {
            const grid = document.getElementById('availability-grid');
            const dateRange = @json($dateRange);

            // Clear existing content
            grid.innerHTML = '';

            // Create time slots (9am to 6pm)
            const timeSlots = [];
            for (let hour = 9; hour <= 18; hour++) {
                timeSlots.push(`${hour}:00`);
                if (hour < 18) timeSlots.push(`${hour}:30`);
            }

            // Generate rows for each time slot
            timeSlots.forEach(timeSlot => {
                const row = document.createElement('tr');

                // Time column
                const timeCell = document.createElement('td');
                timeCell.className = 'py-2 px-4 border-b border-gray-200';
                timeCell.textContent = timeSlot;
                row.appendChild(timeCell);

                // Day columns
                dateRange.forEach(date => {
                    const dayCell = document.createElement('td');
                    dayCell.className = 'py-2 px-4 border-b border-gray-200 text-center';

                    // Check if staff is available at this time
                    const dayName = new Date(date.date).toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
                    const isWorkDay = staffMember.work_days && staffMember.work_days.includes(dayName);

                    if (isWorkDay) {
                        // Check if time is within working hours
                        const [hour, minute] = timeSlot.split(':').map(Number);
                        const slotTime = new Date();
                        slotTime.setHours(hour, minute, 0);

                        const startTime = new Date(staffMember.work_start_time);
                        const endTime = new Date(staffMember.work_end_time);

                        if (slotTime >= startTime && slotTime <= endTime) {
                            dayCell.className += ' bg-green-100';
                            dayCell.innerHTML = '<span class="text-green-600">Available</span>';
                        } else {
                            dayCell.className += ' bg-gray-100';
                            dayCell.innerHTML = '<span class="text-gray-400">Off</span>';
                        }
                    } else {
                        dayCell.className += ' bg-gray-100';
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

            const form = this;
            const formData = new FormData(form);
            const staffId = formData.get('staff_id');
            const submitButton = form.querySelector('button[type="submit"]');

            // Disable submit button to prevent double submission
            submitButton.disabled = true;
            submitButton.innerHTML = 'Saving...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the staff data in memory
                    const staffMember = staffData.find(s => s.id == staffId);
                    if (staffMember) {
                        staffMember.work_days = data.work_days || [];
                        staffMember.work_start_time = data.work_start_time || '';
                        staffMember.work_end_time = data.work_end_time || '';

                        // Update the grid
                        updateAvailabilityGrid(staffMember);

                        // Show success message
                        alert('Availability updated successfully!');
                    }
                } else {
                    alert(data.message || 'Error updating availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = error.message || 'An error occurred while updating availability';
                alert(errorMessage);
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Availability';
            });
        });
    });
</script>
@endpush
@endsection
