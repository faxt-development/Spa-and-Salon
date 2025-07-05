<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminNewTrialNotification;
use App\Mail\WelcomeNewUser;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;

        // Set the Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Handle incoming Stripe webhooks
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');
Log::info('Stripe Webhook received');
Log::info($payload);
        try {
            $event = Webhook::constructEvent(
                $request->getContent(), $sigHeader, $webhookSecret
            );
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            // Add more event types as needed
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle successful payment intent
     *
     * @param  \Stripe\PaymentIntent  $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Check if this is a gift card purchase
        if (($paymentIntent->metadata->type ?? '') === 'gift_card') {
            try {
                // Get the gift card data from metadata
                $giftCardData = [
                    'amount' => $paymentIntent->amount / 100, // Convert from cents
                    'recipient_name' => $paymentIntent->metadata->recipient_name ?? '',
                    'recipient_email' => $paymentIntent->metadata->recipient_email ?? '',
                    'sender_name' => $paymentIntent->metadata->sender_name ?? '',
                    'message' => $paymentIntent->metadata->message ?? null,
                    'expires_at' => $paymentIntent->metadata->expires_at ?? null,
                ];

                // Create the gift card
                $giftCard = $this->paymentService->handleSuccessfulGiftCardPayment(
                    $paymentIntent->id,
                    $giftCardData
                );

                // Send email notification
                // This will be implemented in the next step
                // $this->sendGiftCardEmail($giftCard);

                Log::info('Gift card created via webhook', [
                    'gift_card_id' => $giftCard->id,
                    'payment_intent_id' => $paymentIntent->id
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing successful payment webhook: ' . $e->getMessage(), [
                    'payment_intent_id' => $paymentIntent->id,
                    'exception' => $e
                ]);
            }
        }
    }

    /**
     * Handle failed payment intent
     *
     * @param  \Stripe\PaymentIntent  $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        // Log failed payment attempts
        Log::warning('Payment failed', [
            'payment_intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
            'last_payment_error' => $paymentIntent->last_payment_error ?? null,
        ]);
    }

    /**
     * Handle charge refunded
     *
     * @param  \Stripe\Charge  $charge
     * @return void
     */
    protected function handleChargeRefunded($charge)
    {
        // Handle refunds if needed
        Log::info('Charge refunded', [
            'charge_id' => $charge->id,
            'amount' => $charge->amount_refunded / 100,
            'payment_intent_id' => $charge->payment_intent ?? null,
        ]);
    }

    /**
     * Handle checkout session completed event
     * This is triggered when a customer completes the checkout process
     *
     * @param  \Stripe\Checkout\Session  $session
     * @return void
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        Log::info('Checkout session completed', [
            'session_id' => $session->id,
            'customer_email' => $session->customer_details->email ?? null,
            'customer_name' => $session->customer_details->name ?? null,
        ]);

        try {
            // Get customer details from the session
            $customerEmail = $session->customer_details->email ?? null;
            $customerName = $session->customer_details->name ?? null;

            if (!$customerEmail) {
                Log::error('No customer email found in checkout session', ['session_id' => $session->id]);
                return;
            }

            // Check if this is a subscription checkout
            if ($session->mode !== 'subscription') {
                Log::info('Not a subscription checkout, skipping user creation', ['session_id' => $session->id]);
                return;
            }

            // Generate a random password for the new user
            $temporaryPassword = Str::random(12);

            // Check if user already exists
            $user = User::where('email', $customerEmail)->first();

            if (!$user) {
                // Create a new user
                $user = User::create([
                    'name' => $customerName ?? explode('@', $customerEmail)[0],
                    'email' => $customerEmail,
                    'password' => Hash::make($temporaryPassword),
                    'email_notifications' => true,
                    'onboarding_completed' => false, // Mark as not completed onboarding
                    'stripe_session_id' => $session->id, // Store the session ID
                ]);

                Log::info('Created new user for subscription', [
                    'user_id' => $user->id,
                    'session_id' => $session->id
                ]);
            } else {
                // Update existing user with session ID
                $user->update(['stripe_session_id' => $session->id]);
                Log::info('User already exists, using existing user', [
                    'user_id' => $user->id,
                    'session_id' => $session->id
                ]);
                $temporaryPassword = null; // Don't send password for existing users
            }

            // Assign admin role to the user
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole && !$user->hasRole('admin')) {
                $user->assignRole($adminRole);
                Log::info('Assigned admin role to user', ['user_id' => $user->id]);
            }

            // Find the plan based on the price ID
            $priceId = $session->line_items->data[0]->price->id ?? null;
            $plan = Plan::where('stripe_plan_id', $priceId)->first();

            if (!$plan) {
                Log::warning('Plan not found for price ID', ['price_id' => $priceId]);
                // Create a default plan record if not found
                $plan = Plan::create([
                    'name' => 'Subscription Plan',
                    'slug' => 'subscription-plan',
                    'stripe_plan_id' => $priceId,
                    'price' => $session->amount_total / 100,
                    'currency' => $session->currency ?? 'usd',
                    'is_active' => true,
                ]);
            }

            // Retrieve the subscription details from Stripe
            $stripeSubscription = null;
            $trialEndsAt = null;
            
            try {
                if ($session->subscription) {
                    $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);
                    
                    // Check if the subscription has a trial end date
                    if (isset($stripeSubscription->trial_end) && $stripeSubscription->trial_end > 0) {
                        $trialEndsAt = date('Y-m-d H:i:s', $stripeSubscription->trial_end);
                        Log::info('Trial end date found', ['trial_end' => $trialEndsAt]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error retrieving subscription from Stripe', [
                    'subscription_id' => $session->subscription,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Create or update subscription record
            $subscription = Subscription::updateOrCreate(
                ['stripe_id' => $session->subscription],
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'name' => $plan->name,
                    'stripe_status' => 'active',
                    'stripe_price' => $priceId,
                    'quantity' => 1,
                    'trial_ends_at' => $trialEndsAt,
                ]
            );

            Log::info('Created/updated subscription record', ['subscription_id' => $subscription->id]);

            // Store the session ID for the onboarding process
            // This will be used to redirect the user to the onboarding flow
            // when they click the link in the welcome email
            $onboardingUrl = route('onboarding.start', ['session_id' => $session->id]);

            // Send welcome email to the new user with onboarding link
            Mail::to($user->email)->send(new WelcomeNewUser($user, $temporaryPassword, $onboardingUrl));
            Log::info('Sent welcome email to user with onboarding link', [
                'user_id' => $user->id,
                'onboarding_url' => $onboardingUrl
            ]);

            // Send notification to admin
            $adminEmails = config('services.admin_notification_emails', ['admin@faxtina.com']);
            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminNewTrialNotification($user, $subscription));
            }
            Log::info('Sent admin notification emails', ['admin_emails' => $adminEmails]);

        } catch (\Exception $e) {
            Log::error('Error processing checkout session: ' . $e->getMessage(), [
                'session_id' => $session->id,
                'exception' => $e,
            ]);
        }
    }
}
