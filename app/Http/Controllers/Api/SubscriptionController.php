<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Create a checkout session for subscription
     * This endpoint is called from the marketing site
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'priceId' => 'required|string',
                'firstMonthFree' => 'boolean',
                'successUrl' => 'required|string',  // Changed from 'url' to 'string' to allow Stripe placeholders
                'cancelUrl' => 'required|string',   // Changed from 'url' to 'string' to allow Stripe placeholders
            ]);
            
            // Custom validation for URLs with Stripe placeholders
            $successUrl = $request->input('successUrl');
            $cancelUrl = $request->input('cancelUrl');
            
            // Remove Stripe placeholders for URL validation
            $successUrlForValidation = str_replace('{CHECKOUT_SESSION_ID}', 'session_id', $successUrl);
            
            // Validate URLs after removing placeholders
            if (!filter_var($successUrlForValidation, FILTER_VALIDATE_URL)) {
                throw new \Exception('The success URL is not valid');
            }
            
            if (!filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('The cancel URL is not valid');
            }

            // Get request data
            $priceId = $request->input('priceId');
            $firstMonthFree = $request->input('firstMonthFree', false);
            $successUrl = $request->input('successUrl');
            $cancelUrl = $request->input('cancelUrl');

            Log::info('Creating checkout session', [
                'priceId' => $priceId,
                'firstMonthFree' => $firstMonthFree,
            ]);

            // Create session options
            $sessionOptions = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];

            // Add trial period if firstMonthFree is true
            if ($firstMonthFree) {
                $sessionOptions['subscription_data'] = [
                    'trial_period_days' => 30, // 30-day free trial
                ];
                Log::info('Adding 30-day free trial to subscription');
            }

            // Create the checkout session
            $session = $this->stripe->checkout->sessions->create($sessionOptions);

            Log::info('Created checkout session', ['sessionId' => $session->id]);

            return response()->json(['sessionId' => $session->id]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage(), [
                'error' => $e->getError(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Error creating checkout session: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
