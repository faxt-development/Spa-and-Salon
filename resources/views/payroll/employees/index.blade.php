@extends('layouts.app')

@section('content')
<div class="container" x-data="employeeManagement()">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee Management</h5>
                    <button class="btn btn-primary" @click="openCreateModal">Add Employee</button>
                </div>

                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Search by name or email" 
                                x-model="filters.search" @input="debounceSearch">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" x-model="filters.employment_type" @change="loadEmployees">
                                <option value="">All Employment Types</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" x-model="filters.is_active" @change="loadEmployees">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary w-100" @click="resetFilters">Reset</button>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div class="text-center my-3" x-show="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Employees Table -->
                    <div class="table-responsive" x-show="!loading && employees.data && employees.data.length > 0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th @click="sort('last_name')">
                                        Name
                                        <span x-show="sortField === 'last_name'">
                                            <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></i>
                                        </span>
                                    </th>
                                    <th>Position</th>
                                    <th @click="sort('employment_type')">
                                        Type
                                        <span x-show="sortField === 'employment_type'">
                                            <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></i>
                                        </span>
                                    </th>
                                    <th>Contact</th>
                                    <th>Compensation</th>
                                    <th @click="sort('hire_date')">
                                        Hire Date
                                        <span x-show="sortField === 'hire_date'">
                                            <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"></i>
                                        </span>
                                    </th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="employee in employees.data" :key="employee.id">
                                    <tr>
                                        <td>
                                            <span x-text="employee.first_name + ' ' + employee.last_name"></span>
                                            <span x-show="employee.staff" class="badge bg-info ms-1">Staff</span>
                                        </td>
                                        <td x-text="employee.position"></td>
                                        <td>
                                            <span class="badge" 
                                                :class="{
                                                    'bg-primary': employee.employment_type === 'full-time',
                                                    'bg-success': employee.employment_type === 'part-time',
                                                    'bg-warning': employee.employment_type === 'contract'
                                                }"
                                                x-text="formatEmploymentType(employee.employment_type)">
                                            </span>
                                        </td>
                                        <td>
                                            <div x-text="employee.email"></div>
                                            <div x-text="employee.phone"></div>
                                        </td>
                                        <td>
                                            <div x-show="employee.hourly_rate">
                                                $<span x-text="employee.hourly_rate"></span>/hr
                                            </div>
                                            <div x-show="employee.salary">
                                                $<span x-text="employee.salary"></span>/year
                                            </div>
                                            <div x-text="formatPaymentFrequency(employee.payment_frequency)"></div>
                                        </td>
                                        <td x-text="formatDate(employee.hire_date)"></td>
                                        <td>
                                            <span class="badge" 
                                                :class="employee.is_active ? 'bg-success' : 'bg-danger'"
                                                x-text="employee.is_active ? 'Active' : 'Inactive'">
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" @click="viewEmployee(employee)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" @click="editEmployee(employee)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm" 
                                                    :class="employee.is_active ? 'btn-danger' : 'btn-success'"
                                                    @click="toggleEmployeeStatus(employee)">
                                                    <i class="fas" :class="employee.is_active ? 'fa-user-slash' : 'fa-user-check'"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div class="text-center py-5" x-show="!loading && (!employees.data || employees.data.length === 0)">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>No employees found</h5>
                        <p class="text-muted">Try adjusting your search or filters, or add a new employee.</p>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4" x-show="!loading && employees.data && employees.data.length > 0">
                        <ul class="pagination justify-content-center">
                            <template x-for="link in employees.links" :key="link.label">
                                <li class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(link.url)" x-html="link.label"></a>
                                </li>
                            </template>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee View Modal -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" x-show="selectedEmployee">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <p><strong>Name:</strong> <span x-text="selectedEmployee?.first_name + ' ' + selectedEmployee?.last_name"></span></p>
                            <p><strong>Email:</strong> <span x-text="selectedEmployee?.email"></span></p>
                            <p><strong>Phone:</strong> <span x-text="selectedEmployee?.phone || 'N/A'"></span></p>
                            <p><strong>Address:</strong> <span x-text="selectedEmployee?.address || 'N/A'"></span></p>
                            <p><strong>Emergency Contact:</strong> <span x-text="selectedEmployee?.emergency_contact || 'N/A'"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Employment Information</h6>
                            <p><strong>Position:</strong> <span x-text="selectedEmployee?.position"></span></p>
                            <p><strong>Type:</strong> <span x-text="formatEmploymentType(selectedEmployee?.employment_type)"></span></p>
                            <p><strong>Hire Date:</strong> <span x-text="formatDate(selectedEmployee?.hire_date)"></span></p>
                            <p><strong>Status:</strong> 
                                <span class="badge" 
                                    :class="selectedEmployee?.is_active ? 'bg-success' : 'bg-danger'"
                                    x-text="selectedEmployee?.is_active ? 'Active' : 'Inactive'">
                                </span>
                            </p>
                            <template x-if="selectedEmployee?.termination_date">
                                <p><strong>Termination Date:</strong> <span x-text="formatDate(selectedEmployee?.termination_date)"></span></p>
                            </template>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Compensation</h6>
                            <template x-if="selectedEmployee?.hourly_rate">
                                <p><strong>Hourly Rate:</strong> $<span x-text="selectedEmployee?.hourly_rate"></span>/hour</p>
                            </template>
                            <template x-if="selectedEmployee?.salary">
                                <p><strong>Salary:</strong> $<span x-text="selectedEmployee?.salary"></span>/year</p>
                            </template>
                            <p><strong>Payment Frequency:</strong> <span x-text="formatPaymentFrequency(selectedEmployee?.payment_frequency)"></span></p>
                            <p><strong>Tax ID:</strong> <span x-text="selectedEmployee?.tax_id || 'N/A'"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>System Information</h6>
                            <p><strong>User Account:</strong> <span x-text="selectedEmployee?.user ? 'Yes' : 'No'"></span></p>
                            <p><strong>Staff Profile:</strong> <span x-text="selectedEmployee?.staff ? 'Yes' : 'No'"></span></p>
                            <template x-if="selectedEmployee?.staff">
                                <p><strong>Staff Position:</strong> <span x-text="selectedEmployee?.staff?.position"></span></p>
                            </template>
                            <p><strong>Notes:</strong> <span x-text="selectedEmployee?.notes || 'N/A'"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" @click="editEmployee(selectedEmployee)">Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function employeeManagement() {
        return {
            employees: {},
            selectedEmployee: null,
            loading: true,
            filters: {
                search: '',
                employment_type: '',
                is_active: '',
                page: 1
            },
            sortField: 'last_name',
            sortDirection: 'asc',
            searchTimeout: null,
            viewModal: null,
            
            init() {
                this.loadEmployees();
                this.viewModal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
            },
            
            loadEmployees() {
                this.loading = true;
                
                let queryParams = new URLSearchParams();
                if (this.filters.search) queryParams.append('search', this.filters.search);
                if (this.filters.employment_type) queryParams.append('employment_type', this.filters.employment_type);
                if (this.filters.is_active !== '') queryParams.append('is_active', this.filters.is_active);
                queryParams.append('sort_field', this.sortField);
                queryParams.append('sort_direction', this.sortDirection);
                queryParams.append('page', this.filters.page);
                
                fetch(`/api/employees?${queryParams.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.employees = data.data;
                            this.loading = false;
                        } else {
                            console.error('Error loading employees:', data.message);
                            this.loading = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.loading = false;
                    });
            },
            
            debounceSearch() {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.loadEmployees();
                }, 300);
            },
            
            resetFilters() {
                this.filters = {
                    search: '',
                    employment_type: '',
                    is_active: '',
                    page: 1
                };
                this.loadEmployees();
            },
            
            sort(field) {
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
                this.loadEmployees();
            },
            
            goToPage(url) {
                if (!url) return;
                
                const urlObj = new URL(url);
                this.filters.page = urlObj.searchParams.get('page') || 1;
                this.loadEmployees();
            },
            
            viewEmployee(employee) {
                this.selectedEmployee = employee;
                this.viewModal.show();
            },
            
            editEmployee(employee) {
                // Redirect to edit page
                window.location.href = `/payroll/employees/${employee.id}/edit`;
            },
            
            toggleEmployeeStatus(employee) {
                if (confirm(`Are you sure you want to ${employee.is_active ? 'deactivate' : 'activate'} this employee?`)) {
                    fetch(`/api/employees/${employee.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            is_active: !employee.is_active,
                            termination_date: employee.is_active ? new Date().toISOString().split('T')[0] : null
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.loadEmployees();
                        } else {
                            alert('Error updating employee status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating employee status.');
                    });
                }
            },
            
            formatEmploymentType(type) {
                if (!type) return 'N/A';
                return type.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            },
            
            formatPaymentFrequency(frequency) {
                if (!frequency) return 'N/A';
                
                const formats = {
                    'weekly': 'Weekly',
                    'bi-weekly': 'Bi-Weekly',
                    'monthly': 'Monthly'
                };
                
                return formats[frequency] || frequency;
            },
            
            formatDate(dateString) {
                if (!dateString) return 'N/A';
                return new Date(dateString).toLocaleDateString();
            },
            
            openCreateModal() {
                window.location.href = '/payroll/employees/create';
            }
        };
    }
</script>
@endsection
