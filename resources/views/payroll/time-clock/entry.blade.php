@extends('layouts.app')

@section('content')
<div class="container" x-data="timeClockEntry()">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Employee Time Clock</h5>
                </div>

                <div class="card-body">
                    <!-- Employee Selection -->
                    <div class="mb-4">
                        <label for="employee_id" class="form-label">Select Employee</label>
                        <select class="form-select" id="employee_id" x-model="selectedEmployeeId" @change="loadEmployeeStatus">
                            <option value="">Select Employee</option>
                            <template x-for="employee in employees" :key="employee.id">
                                <option :value="employee.id" x-text="employee.first_name + ' ' + employee.last_name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Loading State -->
                    <div class="text-center my-4" x-show="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading employee status...</p>
                    </div>

                    <!-- Error Message -->
                    <div class="alert alert-danger" x-show="error" x-text="error"></div>

                    <!-- Employee Status -->
                    <div x-show="!loading && selectedEmployeeId && !error">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1" x-text="employeeStatus?.employee?.first_name + ' ' + employeeStatus?.employee?.last_name"></h5>
                                        <p class="text-muted mb-0" x-text="employeeStatus?.employee?.position"></p>
                                        
                                        <!-- Staff Badge -->
                                        <div class="mt-2" x-show="employeeStatus?.employee?.staff">
                                            <span class="badge bg-info">
                                                <i class="fas fa-user-tie me-1"></i>
                                                Staff Member
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge fs-6" :class="employeeStatus?.is_clocked_in ? 'bg-success' : 'bg-secondary'">
                                            <i class="fas" :class="employeeStatus?.is_clocked_in ? 'fa-clock' : 'fa-user-clock'"></i>
                                            <span x-text="employeeStatus?.is_clocked_in ? 'Currently Working' : 'Not Clocked In'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Time -->
                        <div class="text-center mb-4">
                            <h3 class="mb-0" x-text="currentTime"></h3>
                            <p class="text-muted" x-text="currentDate"></p>
                        </div>

                        <!-- Clock In/Out Button -->
                        <div class="d-grid gap-2">
                            <button 
                                class="btn btn-lg" 
                                :class="employeeStatus?.is_clocked_in ? 'btn-danger' : 'btn-success'"
                                @click="toggleClockStatus"
                                :disabled="processingAction"
                            >
                                <span x-show="processingAction" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                <i class="fas" :class="employeeStatus?.is_clocked_in ? 'fa-sign-out-alt' : 'fa-sign-in-alt'"></i>
                                <span x-text="employeeStatus?.is_clocked_in ? 'Clock Out' : 'Clock In'"></span>
                            </button>
                        </div>

                        <!-- Active Entry Details -->
                        <div class="mt-4" x-show="employeeStatus?.is_clocked_in && employeeStatus?.active_entry">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Current Session</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Clock In:</strong> <span x-text="formatDateTime(employeeStatus?.active_entry?.clock_in)"></span></p>
                                            <p class="mb-0"><strong>Duration:</strong> <span x-text="calculateDuration(employeeStatus?.active_entry?.clock_in)"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-0"><strong>Notes:</strong> <span x-text="employeeStatus?.active_entry?.notes || 'None'"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Time Clock Entries -->
                        <div class="mt-4" x-show="recentEntries.length > 0">
                            <h6>Recent Time Clock Entries</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Clock In</th>
                                            <th>Clock Out</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="entry in recentEntries" :key="entry.id">
                                            <tr>
                                                <td x-text="formatDate(entry.clock_in)"></td>
                                                <td x-text="formatTime(entry.clock_in)"></td>
                                                <td x-text="entry.clock_out ? formatTime(entry.clock_out) : '-'"></td>
                                                <td x-text="entry.hours ? entry.hours.toFixed(2) : '-'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="text-center py-5" x-show="!loading && !selectedEmployeeId">
                        <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                        <h5>Select an employee to continue</h5>
                        <p class="text-muted">Choose an employee from the dropdown to view their time clock status.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clock Out Modal -->
    <div class="modal fade" id="clockOutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clock Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="notes" rows="3" x-model="clockOutNotes" placeholder="Enter any notes about this shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="confirmClockOut">
                        <span x-show="processingAction" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Clock Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function timeClockEntry() {
        return {
            employees: [],
            selectedEmployeeId: '',
            employeeStatus: null,
            recentEntries: [],
            loading: false,
            processingAction: false,
            error: '',
            currentTime: '',
            currentDate: '',
            clockOutNotes: '',
            clockOutModal: null,
            timeInterval: null,
            
            init() {
                this.loadEmployees();
                this.updateCurrentTime();
                this.timeInterval = setInterval(() => this.updateCurrentTime(), 1000);
                this.clockOutModal = new bootstrap.Modal(document.getElementById('clockOutModal'));
                
                // Clean up interval when component is destroyed
                this.$cleanup = () => {
                    if (this.timeInterval) {
                        clearInterval(this.timeInterval);
                    }
                };
            },
            
            loadEmployees() {
                fetch('/api/employees?is_active=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.employees = data.data.data;
                        } else {
                            console.error('Error loading employees:', data.message);
                            this.error = 'Failed to load employees. Please try again.';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.error = 'An unexpected error occurred. Please try again.';
                    });
            },
            
            loadEmployeeStatus() {
                if (!this.selectedEmployeeId) {
                    this.employeeStatus = null;
                    this.recentEntries = [];
                    return;
                }
                
                this.loading = true;
                this.error = '';
                
                fetch(`/api/time-clock/status/${this.selectedEmployeeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.employeeStatus = data.data;
                            this.loadRecentEntries();
                        } else {
                            console.error('Error loading employee status:', data.message);
                            this.error = 'Failed to load employee status. Please try again.';
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.error = 'An unexpected error occurred. Please try again.';
                        this.loading = false;
                    });
            },
            
            loadRecentEntries() {
                fetch(`/api/time-clock?employee_id=${this.selectedEmployeeId}&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.recentEntries = data.data.data;
                        } else {
                            console.error('Error loading recent entries:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            },
            
            toggleClockStatus() {
                if (this.employeeStatus?.is_clocked_in) {
                    // Show clock out modal
                    this.clockOutModal.show();
                } else {
                    // Clock in directly
                    this.clockIn();
                }
            },
            
            clockIn() {
                this.processingAction = true;
                
                fetch('/api/time-clock/clock-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_id: this.selectedEmployeeId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.processingAction = false;
                    
                    if (data.success) {
                        this.loadEmployeeStatus();
                    } else {
                        this.error = data.message || 'Failed to clock in. Please try again.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.processingAction = false;
                    this.error = 'An unexpected error occurred. Please try again.';
                });
            },
            
            confirmClockOut() {
                this.processingAction = true;
                
                fetch('/api/time-clock/clock-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_id: this.selectedEmployeeId,
                        notes: this.clockOutNotes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.processingAction = false;
                    this.clockOutModal.hide();
                    this.clockOutNotes = '';
                    
                    if (data.success) {
                        this.loadEmployeeStatus();
                    } else {
                        this.error = data.message || 'Failed to clock out. Please try again.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.processingAction = false;
                    this.clockOutModal.hide();
                    this.error = 'An unexpected error occurred. Please try again.';
                });
            },
            
            updateCurrentTime() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString();
                this.currentDate = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },
            
            calculateDuration(startTime) {
                if (!startTime) return '0:00:00';
                
                const start = new Date(startTime);
                const now = new Date();
                const diff = Math.floor((now - start) / 1000); // difference in seconds
                
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                
                return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            },
            
            formatDateTime(dateTimeString) {
                if (!dateTimeString) return '';
                const date = new Date(dateTimeString);
                return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
            },
            
            formatDate(dateTimeString) {
                if (!dateTimeString) return '';
                return new Date(dateTimeString).toLocaleDateString();
            },
            
            formatTime(dateTimeString) {
                if (!dateTimeString) return '';
                return new Date(dateTimeString).toLocaleTimeString();
            }
        };
    }
</script>
@endsection
