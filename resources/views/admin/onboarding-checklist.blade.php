@extends('layouts.app-content')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Admin Onboarding Checklist</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 mb-8">
            Welcome to your admin dashboard! Follow these 10 essential steps to get started with managing your spa and salon business effectively.
        </p>

        <div class="space-y-6">
            <!-- Step 1 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">1. Profile Setup</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['profile_setup']['complete_profile']) && $checklistItems['profile_setup']['complete_profile'] ? 'checked' : '' }} data-item-key="profile_setup.complete_profile">
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-500 hover:underline">Complete your admin profile information</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['profile_setup']['configure_notifications']) && $checklistItems['profile_setup']['configure_notifications'] ? 'checked' : '' }} data-item-key="profile_setup.configure_notifications">
                        <a href="{{ route('profile.edit') }}#notification-preferences" class="hover:text-blue-500 hover:underline">Configure notification preferences</a>
                    </li>
                </ul>
            </div>

            <!-- Step 2 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">2. Business Configuration</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['business_configuration']['setup_locations']) && $checklistItems['business_configuration']['setup_locations'] ? 'checked' : '' }} data-item-key="business_configuration.setup_locations">
                        <a href="{{ route('admin.locations.index') }}" class="hover:text-blue-500 hover:underline">Set up locations and timezones</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['business_configuration']['setup_business_hours']) && $checklistItems['business_configuration']['setup_business_hours'] ? 'checked' : '' }} data-item-key="business_configuration.setup_business_hours">
                        <a href="{{ route('admin.locations.index') }}" class="hover:text-blue-500 hover:underline">Set up business hours</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['business_configuration']['configure_appointment_settings']) && $checklistItems['business_configuration']['configure_appointment_settings'] ? 'checked' : '' }} data-item-key="business_configuration.configure_appointment_settings">
                        <a href="{{ route('admin.appointments.settings') }}" class="hover:text-blue-500 hover:underline">Configure appointment settings</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="checklist-item mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['business_configuration']['setup_service_categories']) && $checklistItems['business_configuration']['setup_service_categories'] ? 'checked' : '' }} data-item-key="business_configuration.setup_service_categories">
                        <a href="{{ route('admin.services.categories') }}" class="hover:text-blue-500 hover:underline">Set up service categories</a>
                    </li>
                </ul>
            </div>

            <!-- Step 3 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">3. Staff Management</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['staff_management']['add_staff_members']) && $checklistItems['staff_management']['add_staff_members'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.staff.index') }}" class="hover:text-blue-500 hover:underline">Add and manage staff members</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['staff_management']['set_staff_availability']) && $checklistItems['staff_management']['set_staff_availability'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.staff.availability') }}" class="hover:text-blue-500 hover:underline">Set staff availability</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['staff_management']['assign_services_to_staff']) && $checklistItems['staff_management']['assign_services_to_staff'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.staff.services') }}" class="hover:text-blue-500 hover:underline">Assign services to staff</a>
                    </li>
                </ul>
            </div>

            <!-- Step 4 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">4. Services Setup</h2>
                <ul class="space-y-2 text-gray-600">
                <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['services_setup']['set_pricing_durations']) && $checklistItems['services_setup']['set_pricing_durations'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.services') }}" class="hover:text-blue-500 hover:underline">Set pricing and durations</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['services_setup']['configure_service_categories']) && $checklistItems['services_setup']['configure_service_categories'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.services.categories') }}" class="hover:text-blue-500 hover:underline">Configure service categories</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['services_setup']['create_service_packages']) && $checklistItems['services_setup']['create_service_packages'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.services.packages') }}" class="hover:text-blue-500 hover:underline">Create service packages</a>
                    </li>
                </ul>
            </div>

            <!-- Step 5 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">5. Appointment Management</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['appointment_management']['learn_schedule_appointments']) && $checklistItems['appointment_management']['learn_schedule_appointments'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.appointments.learn') }}" class="hover:text-blue-500 hover:underline">Learn to schedule appointments</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['appointment_management']['setup_appointment_reminders']) && $checklistItems['appointment_management']['setup_appointment_reminders'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.appointments.reminders') }}" class="hover:text-blue-500 hover:underline">Set up appointment reminders</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['appointment_management']['configure_cancellation_policies']) && $checklistItems['appointment_management']['configure_cancellation_policies'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.appointments.policies') }}" class="hover:text-blue-500 hover:underline">Configure cancellation policies</a>
                    </li>
                </ul>
            </div>

            <!-- Step 6 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">6. Email Campaigns</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['email_campaigns']['create_welcome_emails']) && $checklistItems['email_campaigns']['create_welcome_emails'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.email.welcome') }}" class="hover:text-blue-500 hover:underline">Create welcome emails</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['email_campaigns']['setup_appointment_reminders']) && $checklistItems['email_campaigns']['setup_appointment_reminders'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.email.reminders') }}" class="hover:text-blue-500 hover:underline">Set up appointment reminders</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['email_campaigns']['configure_marketing_campaigns']) && $checklistItems['email_campaigns']['configure_marketing_campaigns'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.email.campaigns') }}" class="hover:text-blue-500 hover:underline">Configure marketing campaigns</a>
                    </li>
                </ul>
            </div>

            <!-- Step 7 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">7. Payment Processing</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['payment_processing']['configure_payment_methods']) && $checklistItems['payment_processing']['configure_payment_methods'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.payments.methods') }}" class="hover:text-blue-500 hover:underline">Configure payment methods</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['payment_processing']['setup_pricing_rules']) && $checklistItems['payment_processing']['setup_pricing_rules'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.payments.pricing') }}" class="hover:text-blue-500 hover:underline">Set up pricing rules</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['payment_processing']['configure_tax_settings']) && $checklistItems['payment_processing']['configure_tax_settings'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.payments.tax') }}" class="hover:text-blue-500 hover:underline">Configure tax settings</a>
                    </li>
                </ul>
            </div>

            <!-- Step 8 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">8. Reports & Analytics</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['review_sales_reports']) && $checklistItems['reports_analytics']['review_sales_reports'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.reports.sales') }}" class="hover:text-blue-500 hover:underline">Review sales reports</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['analyze_client_spending']) && $checklistItems['reports_analytics']['analyze_client_spending'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('reports.clients.index') }}" class="hover:text-blue-500 hover:underline">Analyze client spending</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['review_service_category_performance']) && $checklistItems['reports_analytics']['review_service_category_performance'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.reports.service.categories') }}" class="hover:text-blue-500 hover:underline">Review service category performance</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['check_payment_method_reports']) && $checklistItems['reports_analytics']['check_payment_method_reports'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.reports.payment-methods') }}" class="hover:text-blue-500 hover:underline">Check payment method reports</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['review_tax_reports']) && $checklistItems['reports_analytics']['review_tax_reports'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.reports.tax') }}" class="hover:text-blue-500 hover:underline">Review tax reports</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['reports_analytics']['setup_payroll_reports']) && $checklistItems['reports_analytics']['setup_payroll_reports'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.payroll.reports.index') }}" class="hover:text-blue-500 hover:underline">Set up payroll reports</a>
                    </li>
                </ul>
            </div>

            <!-- Step 9 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">9. Security & Access</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['security_access']['configure_user_roles_permissions']) && $checklistItems['security_access']['configure_user_roles_permissions'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.staff.roles') }}" class="hover:text-blue-500 hover:underline">Configure user roles & permissions</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['security_access']['update_password_security']) && $checklistItems['security_access']['update_password_security'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('profile.edit') }}" class="hover:text-blue-500 hover:underline">Update password & security settings</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['security_access']['review_company_access_settings']) && $checklistItems['security_access']['review_company_access_settings'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.company.edit') }}" class="hover:text-blue-500 hover:underline">Review company access settings</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['security_access']['manage_staff_access_levels']) && $checklistItems['security_access']['manage_staff_access_levels'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.staff.index') }}" class="hover:text-blue-500 hover:underline">Manage staff access levels</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['security_access']['review_gdpr_compliance_settings']) && $checklistItems['security_access']['review_gdpr_compliance_settings'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('gdpr') }}" class="hover:text-blue-500 hover:underline">Review GDPR compliance settings</a>
                    </li>
                </ul>
            </div>

            <!-- Step 10 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h2 class="text-xl font-semibold mb-2">10. Support & Resources</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['support_resources']['bookmark_support_documentation']) && $checklistItems['support_resources']['bookmark_support_documentation'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.support.docs') }}" class="hover:text-blue-500 hover:underline">Bookmark support documentation</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['support_resources']['setup_emergency_contacts']) && $checklistItems['support_resources']['setup_emergency_contacts'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.support.contacts') }}" class="hover:text-blue-500 hover:underline">Set up emergency contacts</a>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" class="mr-2 h-4 w-4 text-blue-500 rounded border-gray-300 focus:ring-blue-500" {{ isset($checklistItems['support_resources']['review_backup_procedures']) && $checklistItems['support_resources']['review_backup_procedures'] ? 'checked' : '' }} disabled>
                        <a href="{{ route('admin.support.backup') }}" class="hover:text-blue-500 hover:underline">Review backup procedures</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('admin.dashboard') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-bold py-2 px-4 rounded">
                Start Managing Your Business
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all checklist items
        const checklistItems = document.querySelectorAll('.checklist-item');

        // Add event listener to each checkbox
        checklistItems.forEach(item => {
            item.addEventListener('change', function() {
                const itemKey = this.dataset.itemKey;
                const isCompleted = this.checked;

                // Show loading indicator
                this.disabled = true;

                // Send AJAX request to toggle item
                fetch('{{ route("admin.onboarding-checklist.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        item_key: itemKey,
                        is_completed: isCompleted
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success notification
                        console.log('Checklist item updated successfully');
                    } else {
                        // Error handling
                        console.error('Error updating checklist item:', data.message);
                        // Revert checkbox state
                        this.checked = !isCompleted;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert checkbox state
                    this.checked = !isCompleted;
                })
                .finally(() => {
                    // Re-enable checkbox
                    this.disabled = false;
                });
            });
        });
    });
</script>
@endpush
