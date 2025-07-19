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
                    info('redirecting to dashboard');
                    return redirect()->route('admin.dashboard');
                }
            }

        // Sync rule-based completion with JSON field
        $this->syncRuleBasedCompletionWithJson($request);

        }

        return $next($request);
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
