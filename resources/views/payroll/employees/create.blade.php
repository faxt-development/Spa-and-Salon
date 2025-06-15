@extends('layouts.app')

@section('content')
<div class="container" x-data="employeeForm()">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Employee</h5>
                    <a href="{{ route('payroll.employees.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Employees
                    </a>
                </div>

                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <!-- Alert Messages -->
                        <div class="alert alert-danger" x-show="errors.length > 0">
                            <ul class="mb-0">
                                <template x-for="error in errors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="alert alert-success" x-show="successMessage" x-text="successMessage"></div>

                        <!-- Personal Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="first_name" x-model="form.first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="last_name" x-model="form.last_name" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" x-model="form.email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" x-model="form.phone">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" rows="2" x-model="form.address"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control" id="emergency_contact" x-model="form.emergency_contact">
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Employment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="position" class="form-label">Position *</label>
                                        <input type="text" class="form-control" id="position" x-model="form.position" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="employment_type" class="form-label">Employment Type *</label>
                                        <select class="form-select" id="employment_type" x-model="form.employment_type" required>
                                            <option value="">Select Type</option>
                                            <option value="full-time">Full Time</option>
                                            <option value="part-time">Part Time</option>
                                            <option value="contract">Contract</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="hire_date" class="form-label">Hire Date *</label>
                                        <input type="date" class="form-control" id="hire_date" x-model="form.hire_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tax_id" class="form-label">Tax ID / SSN</label>
                                        <input type="text" class="form-control" id="tax_id" x-model="form.tax_id">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="staff_id" class="form-label">Link to Staff Member</label>
                                        <select class="form-select" id="staff_id" x-model="form.staff_id">
                                            <option value="">None (Not a Service Provider)</option>
                                            <template x-for="staff in availableStaff" :key="staff.id">
                                                <option :value="staff.id" x-text="staff.first_name + ' ' + staff.last_name"></option>
                                            </template>
                                        </select>
                                        <small class="form-text text-muted">
                                            Link this employee to an existing staff member for service scheduling and appointments
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Compensation Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Compensation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="compensation_type" class="form-label">Compensation Type *</label>
                                        <select class="form-select" id="compensation_type" x-model="compensationType" required>
                                            <option value="">Select Type</option>
                                            <option value="hourly">Hourly</option>
                                            <option value="salary">Salary</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" x-show="compensationType === 'hourly'">
                                        <label for="hourly_rate" class="form-label">Hourly Rate ($) *</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="hourly_rate" 
                                            x-model="form.hourly_rate" :required="compensationType === 'hourly'">
                                    </div>
                                    <div class="col-md-6 mb-3" x-show="compensationType === 'salary'">
                                        <label for="salary" class="form-label">Annual Salary ($) *</label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="salary" 
                                            x-model="form.salary" :required="compensationType === 'salary'">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_frequency" class="form-label">Payment Frequency *</label>
                                        <select class="form-select" id="payment_frequency" x-model="form.payment_frequency" required>
                                            <option value="">Select Frequency</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="bi-weekly">Bi-Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="overtime_eligible" class="form-label">Overtime Eligible</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="overtime_eligible" x-model="form.overtime_eligible">
                                            <label class="form-check-label" for="overtime_eligible">Eligible for overtime pay</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" rows="3" x-model="form.notes"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" @click="resetForm">Reset</button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span x-show="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Save Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function employeeForm() {
        return {
            form: {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                address: '',
                emergency_contact: '',
                position: '',
                employment_type: '',
                hire_date: new Date().toISOString().split('T')[0],
                tax_id: '',
                staff_id: '',
                hourly_rate: null,
                salary: null,
                payment_frequency: '',
                overtime_eligible: false,
                notes: '',
                is_active: true
            },
            compensationType: '',
            availableStaff: [],
            loading: false,
            errors: [],
            successMessage: '',
            
            init() {
                this.loadAvailableStaff();
            },
            
            loadAvailableStaff() {
                fetch('/api/staff?unassigned=true')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.availableStaff = data.data;
                        } else {
                            console.error('Error loading staff:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            },
            
            submitForm() {
                this.loading = true;
                this.errors = [];
                this.successMessage = '';
                
                // Validate form
                if (!this.validateForm()) {
                    this.loading = false;
                    return;
                }
                
                // Prepare form data based on compensation type
                const formData = { ...this.form };
                if (this.compensationType === 'hourly') {
                    formData.salary = null;
                } else if (this.compensationType === 'salary') {
                    formData.hourly_rate = null;
                }
                
                // Submit form
                fetch('/api/employees', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    
                    if (data.success) {
                        this.successMessage = 'Employee created successfully!';
                        setTimeout(() => {
                            window.location.href = '/payroll/employees';
                        }, 1500);
                    } else {
                        if (data.errors) {
                            Object.values(data.errors).forEach(error => {
                                this.errors.push(error);
                            });
                        } else {
                            this.errors.push(data.message || 'An error occurred while creating the employee.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.loading = false;
                    this.errors.push('An unexpected error occurred. Please try again.');
                });
            },
            
            validateForm() {
                let isValid = true;
                this.errors = [];
                
                // Required fields
                const requiredFields = ['first_name', 'last_name', 'email', 'position', 'employment_type', 'hire_date', 'payment_frequency'];
                requiredFields.forEach(field => {
                    if (!this.form[field]) {
                        this.errors.push(`${field.replace('_', ' ')} is required.`);
                        isValid = false;
                    }
                });
                
                // Compensation validation
                if (!this.compensationType) {
                    this.errors.push('Compensation type is required.');
                    isValid = false;
                } else if (this.compensationType === 'hourly' && !this.form.hourly_rate) {
                    this.errors.push('Hourly rate is required.');
                    isValid = false;
                } else if (this.compensationType === 'salary' && !this.form.salary) {
                    this.errors.push('Salary is required.');
                    isValid = false;
                }
                
                // Email validation
                if (this.form.email && !this.isValidEmail(this.form.email)) {
                    this.errors.push('Please enter a valid email address.');
                    isValid = false;
                }
                
                return isValid;
            },
            
            isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            },
            
            resetForm() {
                this.form = {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    address: '',
                    emergency_contact: '',
                    position: '',
                    employment_type: '',
                    hire_date: new Date().toISOString().split('T')[0],
                    tax_id: '',
                    staff_id: '',
                    hourly_rate: null,
                    salary: null,
                    payment_frequency: '',
                    overtime_eligible: false,
                    notes: '',
                    is_active: true
                };
                this.compensationType = '';
                this.errors = [];
                this.successMessage = '';
            }
        };
    }
</script>
@endsection
