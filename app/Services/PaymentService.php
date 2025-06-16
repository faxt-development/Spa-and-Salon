<?php

namespace App\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;
use App\Models\GiftCard;

class PaymentService
{
    protected $stripe;
    protected $currency;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->currency = config('services.stripe.currency', 'usd');
    }

    /**
     * Create a payment intent for a gift card purchase
     *
     * @param float $amount
     * @param array $metadata
     * @return array
     */
    public function createGiftCardPaymentIntent(float $amount, array $metadata = [])
    {
        try {
            $amountInCents = (int)($amount * 100);
            
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => $this->currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => array_merge($metadata, ['type' => 'gift_card']),
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle successful payment and create gift card
     * 
     * @param \Stripe\PaymentIntent|string $paymentIntentOrId
     * @param array $giftCardData
     * @return GiftCard
     * @throws \Exception
     */
    public function handleSuccessfulGiftCardPayment($paymentIntentOrId, array $giftCardData)
    {
        // If we got a payment intent ID, retrieve it
        if (is_string($paymentIntentOrId)) {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentOrId);
        } else {
            $paymentIntent = $paymentIntentOrId;
        }
        
        if ($paymentIntent->status !== 'succeeded') {
            throw new \Exception('Payment not completed');
        }

        // Extract user_id from metadata if available
        $userId = $giftCardData['user_id'] ?? $paymentIntent->metadata->user_id ?? null;
        
        // Validate required fields
        $requiredFields = ['amount', 'recipient_name', 'recipient_email', 'sender_name'];
        foreach ($requiredFields as $field) {
            if (empty($giftCardData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Create the gift card
        $giftCard = new GiftCard([
            'code' => $this->generateUniqueCode(),
            'amount' => $giftCardData['amount'],
            'balance' => $giftCardData['amount'], // Initial balance equals the amount
            'recipient_name' => $giftCardData['recipient_name'],
            'recipient_email' => $giftCardData['recipient_email'],
            'sender_name' => $giftCardData['sender_name'],
            'sender_email' => $giftCardData['sender_email'] ?? null,
            'message' => $giftCardData['message'] ?? null,
            'expires_at' => $giftCardData['expires_at'] ?? now()->addYear(),
            'is_active' => true,
            'payment_intent_id' => $paymentIntent->id,
        ]);
        
        // Associate with user if available
        if ($userId) {
            $giftCard->user_id = $userId;
        }
        $giftCard->save();

        // Send gift card email to recipient
        try {
            Mail::to($giftCard->recipient_email)
                ->queue(new GiftCardPurchased(
                    $giftCard,
                    $giftCardData['sender_name'],
                    $giftCardData['message'] ?? null
                ));
                
            Log::info('Gift card email sent', [
                'gift_card_id' => $giftCard->id,
                'recipient_email' => $giftCard->recipient_email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send gift card email', [
                'gift_card_id' => $giftCard->id,
                'error' => $e->getMessage()
            ]);
            // Don't fail the whole process if email fails
        }

        return $giftCard;
    }

    /**
     * Generate a unique gift card code
     * 
     * @return string
     */
    protected function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (GiftCard::where('code', $code)->exists());
        
        return $code;
    }

    /**
     * Refund a gift card payment
     * 
     * @param GiftCard $giftCard
     * @param float|null $amount
     * @return array
     */
    public function refundGiftCard(GiftCard $giftCard, ?float $amount = null)
    {
        try {
            if (!$giftCard->payment_intent_id) {
                throw new \Exception('No payment intent associated with this gift card');
            }

            $refundData = [
                'payment_intent' => $giftCard->payment_intent_id,
                'metadata' => [
                    'gift_card_id' => $giftCard->id,
                    'reason' => 'refund'
                ]
            ];

            if ($amount !== null && $amount < $giftCard->amount) {
                $refundData['amount'] = (int)($amount * 100);
            }

            $refund = $this->stripe->refunds->create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
