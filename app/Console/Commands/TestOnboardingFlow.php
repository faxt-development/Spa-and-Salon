<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\StripeWebhookController;
use App\Mail\AdminNewTrialNotification;
use App\Mail\WelcomeNewUser;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TestOnboardingFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-onboarding-flow {email? : The email to use for testing} {name? : The name to use for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the onboarding flow by simulating a Stripe checkout.session.completed event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing onboarding flow...');

        // Get or create a test plan
        $plan = Plan::first();
        if (!$plan) {
            $this->info('Creating test plan...');
            $plan = Plan::create([
                'name' => 'Test Plan',
                'slug' => 'test-plan',
                'stripe_plan_id' => 'test_plan_id',
                'price' => 99.99,
                'currency' => 'usd',
                'is_active' => true,
            ]);
        }

        // Create a test user
        $email = $this->argument('email') ?? 'test_' . Str::random(5) . '@example.com';
        $name = $this->argument('name') ?? 'Test User ' . Str::random(5);

        $this->info("Using email: {$email}");
        $this->info("Using name: {$name}");

        // Create a temporary password for the user
        $temporaryPassword = Str::random(12);

        // Create or find the user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($temporaryPassword),
                'email_notifications' => true,
                'onboarding_completed' => false,
            ]
        );

        // Assign admin role to the user if not already assigned
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        // Create a test subscription with required fields
        $subscriptionId = 'test_subscription_' . time();
        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'stripe_id' => $subscriptionId,
                'stripe_status' => 'active',
                'stripe_price' => 'test_price_id',
                'quantity' => 1,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]
        );

        // Generate session ID for onboarding
        $sessionId = 'test_session_' . time();

        // Generate onboarding URL
        $onboardingUrl = route('onboarding.start', ['session_id' => $sessionId]);

        // Send welcome email with onboarding link
        try {
            // Log mail configuration for debugging
            Log::info('Mail configuration', [
                'driver' => config('mail.default'),
                'from' => config('mail.from'),
                'log_channel' => config('mail.mailers.log.channel')
            ]);

            $this->info('Attempting to send welcome email...');
            Log::info('Attempting to send welcome email', ['to' => $user->email]);

            // Capture the email content for logging
            $welcomeEmail = new WelcomeNewUser($user, $temporaryPassword, $onboardingUrl);

            // Log the email view data
            Log::info('Welcome email view data', [
                'user' => $user->name,
                'email' => $user->email,
                'onboardingUrl' => $onboardingUrl,
                'view' => 'emails.welcome-new-user'
            ]);

            // Send the email
           // Mail::to($user->email)->send($welcomeEmail);
           Mail::mailer('log')->to($user->email)->send($welcomeEmail);

            $this->info('Welcome email sent successfully!');
            Log::info('Welcome email sent successfully', ['to' => $user->email]);

            $this->info('Onboarding flow initiated successfully!');
            $this->info("Onboarding URL: {$onboardingUrl}");
            $this->info("Email: {$email}");
            $this->info("Password: {$temporaryPassword}");

            // Send notification to admin
            $adminEmails = config('services.admin_notification_emails', ['info@faxt.com']);
            $this->info('Sending admin notifications to: ' . implode(', ', $adminEmails));
            Log::info('Sending admin notifications', ['to' => $adminEmails]);

            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminNewTrialNotification($user, $subscription));
                Log::info('Admin notification sent', ['to' => $adminEmail]);
            }

            $this->info('Admin notification emails sent to: ' . implode(', ', $adminEmails));

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Test onboarding flow error: ' . $e->getMessage());
        }
    }
}
