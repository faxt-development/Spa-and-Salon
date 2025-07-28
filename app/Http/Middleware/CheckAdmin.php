<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }
        // Track onboarding checklist visits
        $this->trackOnboardingChecklistVisit($request);

        return $next($request);
    }


    /**
     * Track when a user visits a page linked from the onboarding checklist
     * and mark that item as completed in the JSON field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function trackOnboardingChecklistVisit(Request $request): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        $currentRoute = $request->route()->getName();
        // Map of route names to onboarding checklist item keys
        $routeToChecklistMap = [
            'profile.edit' => 'profile_setup.complete_profile',
            'profile.edit#notification-preferences' => 'profile_setup.configure_notifications',
            'admin.locations.index' => ['business_configuration.setup_locations', 'business_configuration.setup_business_hours'],
            'admin.appointments.settings' => 'business_configuration.configure_appointment_settings',
            'admin.services.categories' => ['business_configuration.setup_service_categories', 'services_setup.configure_service_categories'],
            'admin.staff.index' => 'staff_management.add_staff_members',
            'admin.staff.availability' => 'staff_management.set_staff_availability',
            'admin.staff.services' => 'staff_management.assign_services_to_staff',
            'admin.services' => 'services_setup.set_pricing_durations',
            'admin.services.packages' => 'services_setup.create_service_packages',
            'admin.services.packages' => 'services_setup.create_service_packages',
            'admin.appointments.learn' => 'appointment_management.learn_schedule_appointments',
            'admin.appointments.reminders' => 'appointment_management.setup_appointment_reminders',
            'admin.appointments.policies' => 'appointment_management.configure_cancellation_policies',
            'admin.email.welcome' => 'email_campaigns.create_welcome_emails',
            'admin.email.reminders' => 'email_campaigns.setup_appointment_reminders',
            'admin.email.campaigns' => 'email_campaigns.configure_marketing_campaigns',
            'admin.payments.methods' => 'payment_processing.configure_payment_methods',
            'admin.payments.pricing' => 'payment_processing.setup_pricing_rules',
            'admin.payments.tax' => 'payment_processing.configure_tax_settings',
            'admin.reports.sales' => 'reports_analytics.review_sales_reports',
            'reports.clients.index' => 'reports_analytics.analyze_client_spending',
            'admin.reports.service.categories' => 'reports_analytics.review_service_category_performance',
            'admin.reports.payment-methods' => 'reports_analytics.check_payment_method_reports',
            'admin.reports.tax' => 'reports_analytics.review_tax_reports',
            'admin.payroll.reports.index' => 'reports_analytics.setup_payroll_reports',
            'admin.staff.roles' => 'security_access.configure_user_roles_permissions',
            'admin.company.edit' => 'security_access.review_company_access_settings',
            'gdpr' => 'security_access.review_gdpr_compliance_settings',
            'admin.support.docs' => 'support_resources.bookmark_support_documentation',
            'admin.support.contacts' => 'support_resources.setup_emergency_contacts',
            'admin.support.backup' => 'support_resources.review_backup_procedures',
        ];

        // Check if current route is in our map
        if (array_key_exists($currentRoute, $routeToChecklistMap)) {
            $checklistItems = $routeToChecklistMap[$currentRoute];
            $checklistItems = is_array($checklistItems) ? $checklistItems : [$checklistItems];

            // Get current checklist items or initialize empty array
            $onboardingItems = $user->onboarding_checklist_items ?? [];

            // Mark each mapped item as completed
            foreach ($checklistItems as $item) {
                // Use dot notation to set nested array values
                $keys = explode('.', $item);
                $this->setNestedArrayValue($onboardingItems, $keys, true);
            }

            // Save the updated checklist items
            $user->onboarding_checklist_items = $onboardingItems;
            $user->save();
        }

        // Handle fragment identifiers (like profile.edit#notification-preferences)
        $fragment = $request->get('_fragment');
        if ($fragment && $currentRoute) {
            $fragmentRoute = $currentRoute . '#' . $fragment;
            if (array_key_exists($fragmentRoute, $routeToChecklistMap)) {
                $checklistItems = $routeToChecklistMap[$fragmentRoute];
                $checklistItems = is_array($checklistItems) ? $checklistItems : [$checklistItems];

                // Get current checklist items or initialize empty array
                $onboardingItems = $user->onboarding_checklist_items ?? [];

                // Mark each mapped item as completed
                foreach ($checklistItems as $item) {
                    // Use dot notation to set nested array values
                    $keys = explode('.', $item);
                    $this->setNestedArrayValue($onboardingItems, $keys, true);
                }

                // Save the updated checklist items
                $user->onboarding_checklist_items = $onboardingItems;
                $user->save();
            }
        }
    }
  /**
     * Set a value in a nested array using an array of keys
     *
     * @param array &$array  The array to modify
     * @param array $keys    The keys that specify the nested location
     * @param mixed $value   The value to set
     * @return void
     */
    protected function setNestedArrayValue(array &$array, array $keys, $value): void
    {
        $current = &$array;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }

            if (next($keys) === false) {
                $current[$key] = $value;
                return;
            }

            $current = &$current[$key];
        }
    }

}
