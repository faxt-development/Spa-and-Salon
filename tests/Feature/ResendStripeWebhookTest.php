<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Stripe\Webhook;

class ResendStripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test resending a failed Stripe webhook
     *
     * @return void
     */
    public function testResendStripeWebhook()
    {
        // The webhook payload from your logs
        $payload = '{
  "id": "evt_1RhbWjJmhER0XpDimi2ckLu8",
  "object": "event",
  "api_version": "2019-05-16",
  "created": 1751742813,
  "data": {
    "object": {
      "id": "cs_test_a1JgpWG2ziAK5wIKWoNBHF41bDvAYpUoayNvDNfFCnzT2CUcTwDELOQ83d",
      "object": "checkout.session",
      "adaptive_pricing": null,
      "after_expiration": null,
      "allow_promotion_codes": null,
      "amount_subtotal": 0,
      "amount_total": 0,
      "automatic_tax": {
        "enabled": false,
        "liability": null,
        "provider": null,
        "status": null
      },
      "billing_address_collection": null,
      "cancel_url": "http://localhost:8000/pricing",
      "client_reference_id": null,
      "client_secret": null,
      "collected_information": {
        "shipping_details": null
      },
      "consent": null,
      "consent_collection": null,
      "created": 1751742791,
      "currency": "usd",
      "currency_conversion": null,
      "custom_fields": [],
      "custom_text": {
        "after_submit": null,
        "shipping_address": null,
        "submit": null,
        "terms_of_service_acceptance": null
      },
      "customer": "cus_ScrF5XaQCbYvxQ",
      "customer_creation": "always",
      "customer_details": {
        "address": {
          "city": null,
          "country": "MX",
          "line1": null,
          "line2": null,
          "postal_code": null,
          "state": null
        },
        "email": "bcp@faxt.com",
        "name": "Bobbi Perreault",
        "phone": null,
        "tax_exempt": "none",
        "tax_ids": []
      },
      "customer_email": null,
      "discounts": [],
      "expires_at": 1751829191,
      "invoice": "in_1RhbWiJmhER0XpDimNuAfDqO",
      "invoice_creation": null,
      "livemode": false,
      "locale": null,
      "metadata": {},
      "mode": "subscription",
      "origin_context": null,
      "payment_intent": null,
      "payment_link": null,
      "payment_method_collection": "always",
      "payment_method_configuration_details": null,
      "payment_method_options": {
        "card": {
          "request_three_d_secure": "automatic"
        }
      },
      "payment_method_types": [
        "card"
      ],
      "payment_status": "paid",
      "permissions": null,
      "phone_number_collection": {
        "enabled": false
      },
      "recovered_from": null,
      "saved_payment_method_options": {
        "allow_redisplay_filters": [
          "always"
        ],
        "payment_method_remove": "disabled",
        "payment_method_save": null
      },
      "setup_intent": "seti_1RhbWhJmhER0XpDi5RapnJ9j",
      "shipping": null,
      "shipping_address_collection": null,
      "shipping_options": [],
      "shipping_rate": null,
      "status": "complete",
      "submit_type": null,
      "subscription": "sub_1RhbWiJmhER0XpDicNc8kGVY",
      "success_url": "http://localhost:8000/success?session_id={CHECKOUT_SESSION_ID}",
      "total_details": {
        "amount_discount": 0,
        "amount_shipping": 0,
        "amount_tax": 0
      },
      "ui_mode": "hosted",
      "url": null,
      "wallet_options": null
    }
  },
  "livemode": false,
  "pending_webhooks": 2,
  "request": {
    "id": null,
    "idempotency_key": null
  },
  "type": "checkout.session.completed"
}';

        // Get the webhook secret from environment or config
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET') ?? config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            $this->markTestSkipped(
                'Stripe webhook secret is not configured. ' . PHP_EOL .
                'Please set STRIPE_WEBHOOK_SECRET in your .env.testing file:' . PHP_EOL .
                '1. Copy .env to .env.testing if not exists' . PHP_EOL .
                '2. Add STRIPE_WEBHOOK_SECRET=your_webhook_secret to .env.testing' . PHP_EOL .
                '3. Or add it to phpunit.xml in the <php> section'
            );
        }

        // Generate the signature - use the exact payload string as received
        $timestamp = time();
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $webhookSecret);

        // Log the signature details for debugging
        dump('Generated signature:', $signature);
        dump('Signed payload:', $signedPayload);

        // Make the request to your webhook endpoint
        // Send the raw JSON payload instead of the decoded/encoded array
        $response = $this->call(
            'POST',
            '/api/stripe/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}"
            ],
            $payload // Send the raw JSON string
        );

        // Dump the response for debugging
        if ($response->status() !== 200) {
            dump('Response status:', $response->status());
            dump('Response content:', $response->getContent());
        }

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Add additional assertions based on what should happen when this webhook is processed
        // For example, you might want to check if a user was created or a subscription was updated
        $this->assertDatabaseHas('users', [
            'email' => 'bcp@faxt.com'
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'stripe_id' => 'sub_1RhaStJmhER0XpDiEYmWQkNa',
            'stripe_status' => 'active'
        ]);
    }
}
