<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
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
}
