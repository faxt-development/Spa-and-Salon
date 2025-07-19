<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle authenticated users
        if (!Auth::check()) {
            // if url doesn't have a session id or there is no
            //session info in the cookies then redirect to login
            if (!session()->has('session_id') || !session()->has('user_id')) {
             //redirect to the login url
             //do not use the route
             return redirect()->to('/login');
            }
            return $next($request);
        }

        $user = Auth::user();

        // Only apply onboarding logic to admin users with active subscriptions
        $hasAdminRole = $user->hasRole('admin');
        $hasActiveSubscription = $user->hasActiveSubscription();

        if ($hasAdminRole && $hasActiveSubscription) {
            // If admin has not completed onboarding
            if (!$user->onboarding_completed) {
                // If not already on an onboarding route, redirect to onboarding
                if (!$request->routeIs('onboarding.*') && !$request->routeIs('logout')) {
                    \Illuminate\Support\Facades\Log::info('Redirecting to onboarding', ['user_id' => $user->id]);
                    return redirect()->route('onboarding.start');
                }
            } else {
                // If admin has completed onboarding but is on an onboarding route, redirect to dashboard
                if ($request->routeIs('onboarding.*')) {
                    return redirect()->route('admin.dashboard');
                }
            }
        }

        // Track onboarding checklist visits
        $this->trackOnboardingChecklistVisit($request);
        
        // Sync rule-based completion with JSON field
        $this->syncRuleBasedCompletionWithJson($request);
        
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
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
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
    
    /**
     * Sync rule-based checklist completion with the JSON field.
     * If a step is completed according to business rules, mark it as completed in the JSON field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function syncRuleBasedCompletionWithJson(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        $company = $user->primaryCompany();
        
        // Get current checklist items or initialize empty array
        $onboardingItems = $user->onboarding_checklist_items ?? [];
        $updated = false;
        
        // Check for rule-based completion and sync with JSON field
        
        // Profile Setup
        if ($user->name && $user->email) {
            $keys = explode('.', 'profile_setup.complete_profile');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Check if notification preferences have been set
        if (isset($user->sms_notifications) || isset($user->email_notifications) || 
            isset($user->appointment_reminders) || isset($user->promotional_emails)) {
            $keys = explode('.', 'profile_setup.configure_notifications');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Business Configuration
        if ($company && $company->locations()->count() > 0) {
            $keys = explode('.', 'business_configuration.setup_locations');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Staff Management
        if ($company && $company->staff()->count() > 0) {
            $keys = explode('.', 'staff_management.add_staff_members');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Services Setup
        if ($company && $company->serviceCategories()->count() > 0) {
            $keys = explode('.', 'services_setup.configure_service_categories');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        if ($company && $company->services()->count() > 0) {
            $keys = explode('.', 'services_setup.set_pricing_durations');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Payment Processing
        if ($company && $company->paymentMethods()->count() > 0) {
            $keys = explode('.', 'payment_processing.configure_payment_methods');
            if (!$this->isItemCompleted($onboardingItems, $keys)) {
                $this->setNestedArrayValue($onboardingItems, $keys, true);
                $updated = true;
            }
        }
        
        // Save if any updates were made
        if ($updated) {
            $user->onboarding_checklist_items = $onboardingItems;
            $user->save();
        }
    }
    
    /**
     * Check if an item is already completed in the JSON field
     *
     * @param array $array The array to check
     * @param array $keys  The keys that specify the nested location
     * @return bool
     */
    protected function isItemCompleted(array $array, array $keys): bool
    {
        $current = $array;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return false;
            }
            
            if (next($keys) === false) {
                return (bool) $current[$key];
            }
            
            $current = $current[$key];
        }
        
        return false;
    }
}
