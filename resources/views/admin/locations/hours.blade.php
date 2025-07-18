@extends('layouts.app-content')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6 bg-gray-50">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.locations.index') }}" class="mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
    </div>

    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Business Hours</h1>
        <p class="text-gray-500 text-lg">Set your location's operating hours for each day of the week</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    <div className="min-h-screen bg-gradient-to-br from-background via-spa-mint-light/5 to-spa-cream/20">
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="pb-6 p-6 border-b border-gray-100">
            <div class="flex items-center gap-2 text-xl font-bold text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Weekly Schedule
            </div>
            <p class="text-gray-500 text-sm mt-2">
                Configure when your spa is open. You can add multiple time slots for days with breaks.
            </p>
        </div>

        <form action="{{ route('admin.locations.hours.update', $location) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                @php
                    $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                @endphp

                @foreach($dayNames as $index => $dayName)
                    @php
                        $businessHour = $businessHours[$index] ?? null;
                        $isOpen = $businessHour ? !$businessHour->is_closed : false;
                    @endphp
                    <div class="group mb-6">
                        <div class="transition-all duration-200 hover:shadow-sm border border-gray-200 hover:border-gray-300 rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <label for="{{ $dayName }}_open" class="text-xl font-medium min-w-[100px] capitalize text-gray-800">
                                            {{ $dayName }}
                                        </label>
                                        <x-toggle-switch
                                            id="{{ $dayName }}_open"
                                            name="business_hours[{{ $dayName }}][is_open]"
                                            :checked="$isOpen"
                                            show-status
                                        />
                                        <span class="text-sm text-gray-500" id="{{ $dayName }}_status">
                                            {{ $isOpen ? 'Open' : 'Closed' }}
                                        </span>
                                    </div>

                                    <div class="{{ !$isOpen ? 'hidden' : '' }}">
                                        <button type="button" class="text-sm border border-gray-300 rounded px-3 py-1 flex items-center gap-1 hover:bg-gray-50" onclick="addTimeSlot('{{ $dayName }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Hours
                                        </button>
                                    </div>
                                </div>

                                <div id="{{ $dayName }}_hours_container" class="flex flex-col gap-4 {{ !$isOpen ? 'opacity-50 pointer-events-none' : '' }}">
                                    <div id="{{ $dayName }}_closed_message" class="text-gray-500 {{ !$isOpen ? '' : 'hidden' }}">
                                        Closed all day
                                    </div>

                                    <div id="{{ $dayName }}_time_slots" class="space-y-4">
                                        <!-- Default time slot -->
                                        <div class="time-slot bg-gray-50 rounded-lg p-3">
                                            <div class="flex items-center gap-4">
                                                <div class="flex-grow flex items-center gap-4">
                                                    <div>
                                                        <label class="text-sm text-gray-500 block mb-1">From</label>
                                                        <input type="time"
                                                            id="{{ $dayName }}_open_time"
                                                            name="business_hours[{{ $dayName }}][slots][0][open]"
                                                            value="{{ $businessHour && !$businessHour->is_closed ? substr($businessHour->open_time, 0, 5) : '09:00' }}"
                                                            class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                                    </div>
                                                    <div>
                                                        <label class="text-sm text-gray-500 block mb-1">To</label>
                                                        <input type="time"
                                                            id="{{ $dayName }}_close_time"
                                                            name="business_hours[{{ $dayName }}][slots][0][close]"
                                                            value="{{ $businessHour && !$businessHour->is_closed ? substr($businessHour->close_time, 0, 5) : '17:00' }}"
                                                            class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                                    </div>
                                                </div>
                                                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeTimeSlot(this)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 pt-3">
                                        <select class="text-xs px-2 py-1 border rounded bg-white text-gray-500" id="{{ $dayName }}_copy_from">
                                            <option value="">Copy from...</option>
                                            @foreach($dayNames as $sourceDayIndex => $sourceDayName)
                                                @if($sourceDayName != $dayName)
                                                    <option value="{{ $sourceDayName }}">{{ ucfirst($sourceDayName) }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center pt-8">
                <button type="submit" class="px-8 shadow-lg bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-md flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save Business Hours
                </button>
            </div>
        </form>
    </div></div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all day toggles
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        days.forEach(day => {
            const checkbox = document.getElementById(`${day}_open`);
            if (!checkbox) {
                console.error(`Could not find checkbox for ${day}`);
                return;
            }

            // Set up Add Hours button
            try {
                const addHoursBtn = document.querySelector(`#${day}_add_hours`);
                addHoursBtn.addEventListener('click', function() {
                    window.addTimeSlot(day);
                });
            } catch (e) {
                console.error(`Could not find Add Hours button for ${day}:`, e);
            }

            // Set up copy from dropdown
            const copyFromSelect = document.getElementById(`${day}_copy_from`);
            if (copyFromSelect) {
                copyFromSelect.addEventListener('change', function() {
                    const sourceDay = this.value;
                    if (sourceDay && sourceDay !== day) {
                        copyHoursFromDay(sourceDay, day);
                        this.value = ''; // Reset dropdown
                    }
                });
            }

            // Initialize UI based on initial state
            updateDayOpenState(day, checkbox.checked);
        });
        
        // Function to toggle switch state when clicked
        window.toggleSwitch = function(day) {
            const checkbox = document.getElementById(`${day}_open`);
            checkbox.checked = !checkbox.checked;
            updateDayOpenState(day, checkbox.checked);
        };

        // Function to update UI when day open state changes
        function updateDayOpenState(day, isOpen) {
            const checkbox = document.getElementById(`${day}_open`);
            const switchBtn = document.getElementById(`${day}_switch`);
            const switchHandle = document.getElementById(`${day}_switch_handle`);
            const statusText = document.getElementById(`${day}_status`);
            const hoursContainer = document.getElementById(`${day}_hours_container`);
            const closedMessage = document.getElementById(`${day}_closed_message`);
            
            // Update switch appearance
            if (isOpen) {
                switchBtn.classList.remove('bg-gray-200');
                switchBtn.classList.add('bg-green-500');
                switchBtn.setAttribute('aria-checked', 'true');
                switchBtn.setAttribute('data-state', 'checked');
                switchHandle.classList.remove('translate-x-0');
                switchHandle.classList.add('translate-x-5');
                switchHandle.setAttribute('data-state', 'checked');
                statusText.textContent = 'Open';
            } else {
                switchBtn.classList.remove('bg-green-500');
                switchBtn.classList.add('bg-gray-200');
                switchBtn.setAttribute('aria-checked', 'false');
                switchBtn.setAttribute('data-state', 'unchecked');
                switchHandle.classList.remove('translate-x-5');
                switchHandle.classList.add('translate-x-0');
                switchHandle.setAttribute('data-state', 'unchecked');
                statusText.textContent = 'Closed';

                // Hide hours container, show closed message
                hoursContainer.classList.add('hidden');
                closedMessage.classList.remove('hidden');
            }
        }

        // Function to add a new time slot for a day
        window.addTimeSlot = function(day) {
            const timeSlotsContainer = document.getElementById(`${day}_time_slots`);
            const timeSlots = timeSlotsContainer.querySelectorAll('.time-slot');
            const newIndex = timeSlots.length;

            // Create new time slot element
            const newSlot = document.createElement('div');
            newSlot.className = 'time-slot bg-gray-50 rounded-lg p-3';

            // Generate HTML for the new slot
            newSlot.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="flex-grow flex items-center gap-4">
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">From</label>
                            <input type="time"
                                name="business_hours[${day}][slots][${newIndex}][open]"
                                value="09:00"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="text-sm text-gray-500 block mb-1">To</label>
                            <input type="time"
                                name="business_hours[${day}][slots][${newIndex}][close]"
                                value="17:00"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    <button type="button" class="text-red-500 hover:text-red-700" onclick="removeTimeSlot(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;

            // Add the new slot to the container
            timeSlotsContainer.appendChild(newSlot);
        };

        // Function to remove a time slot
        window.removeTimeSlot = function(button) {
            const timeSlot = button.closest('.time-slot');
            const timeSlotsContainer = timeSlot.parentElement;

            // Don't remove if it's the only time slot
            if (timeSlotsContainer.querySelectorAll('.time-slot').length > 1) {
                timeSlot.remove();

                // Renumber the remaining slots for proper form submission
                const remainingSlots = timeSlotsContainer.querySelectorAll('.time-slot');
                remainingSlots.forEach((slot, index) => {
                    const inputs = slot.querySelectorAll('input[type="time"]');
                    inputs.forEach(input => {
                        const nameAttr = input.getAttribute('name');
                        const newName = nameAttr.replace(/\[(\d+)\]/, `[${index}]`);
                        input.setAttribute('name', newName);
                    });
                });
            }
        };

        // Function to copy hours from one day to another
        function copyHoursFromDay(sourceDay, targetDay) {
            const sourceTimeSlotsContainer = document.getElementById(`${sourceDay}_time_slots`);
            const targetTimeSlotsContainer = document.getElementById(`${targetDay}_time_slots`);

            if (sourceTimeSlotsContainer && targetTimeSlotsContainer) {
                // Clear existing time slots in target day
                const targetSlots = targetTimeSlotsContainer.querySelectorAll('.time-slot');
                targetSlots.forEach((slot, index) => {
                    if (index > 0) { // Keep the first slot
                        slot.remove();
                    }
                });

                // Get source day time slots
                const sourceSlots = sourceTimeSlotsContainer.querySelectorAll('.time-slot');

                // Copy first slot times to existing target slot
                if (sourceSlots.length > 0 && targetSlots.length > 0) {
                    const sourceInputs = sourceSlots[0].querySelectorAll('input[type="time"]');
                    const targetInputs = targetSlots[0].querySelectorAll('input[type="time"]');

                    if (sourceInputs.length >= 2 && targetInputs.length >= 2) {
                        targetInputs[0].value = sourceInputs[0].value; // open time
                        targetInputs[1].value = sourceInputs[1].value; // close time
                    }
                }

                // Copy additional slots
                for (let i = 1; i < sourceSlots.length; i++) {
                    const sourceSlot = sourceSlots[i];
                    const sourceInputs = sourceSlot.querySelectorAll('input[type="time"]');

                    // Add a new slot to target day
                    window.addTimeSlot(targetDay);

                    // Get the newly added slot (last one)
                    const newTargetSlots = targetTimeSlotsContainer.querySelectorAll('.time-slot');
                    const newTargetSlot = newTargetSlots[newTargetSlots.length - 1];
                    const newTargetInputs = newTargetSlot.querySelectorAll('input[type="time"]');

                    // Copy times
                    if (sourceInputs.length >= 2 && newTargetInputs.length >= 2) {
                        newTargetInputs[0].value = sourceInputs[0].value; // open time
                        newTargetInputs[1].value = sourceInputs[1].value; // close time
                    }
                }
            }

            // Make sure the target day is open
            const targetCheckbox = document.getElementById(`${targetDay}_open`);
            if (!targetCheckbox.checked) {
                targetCheckbox.checked = true;
                updateDayOpenState(targetDay, true);
            }
        }
    });
</script>
@endpush
@endsection
