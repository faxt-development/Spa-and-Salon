<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ModelStateChanged;
use App\Listeners\LogModelStateChange;

class StateTrackingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge the default configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/state_tracking.php', 'state_tracking'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../../config/state_tracking.php' => config_path('state_tracking.php'),
        ], 'config');

        // Register the event listener
        Event::listen(
            ModelStateChanged::class,
            [LogModelStateChange::class, 'handle']
        );

        // Auto-register tracked models
        $this->registerTrackedModels();
    }

    /**
     * Register the models that should be tracked
     */
    protected function registerTrackedModels(): void
    {
        $models = config('state_tracking.tracked_models', []);

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(\App\Observers\ModelStateObserver::class);
            }
        }
    }
}
