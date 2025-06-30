<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Appointment::class => \App\Policies\AppointmentPolicy::class,
        \App\Models\CommissionStructure::class => \App\Policies\CommissionStructurePolicy::class,
        \App\Models\DashboardWidget::class => \App\Policies\DashboardWidgetPolicy::class,
        \App\Models\UserDashboardPreference::class => \App\Policies\UserDashboardPreferencePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
