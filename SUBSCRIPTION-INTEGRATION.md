# Subscription Integration Guide

## Overview

This document outlines the integration of Stripe subscription checkout in the Faxtina spa-and-salon application. The pricing page has been moved from the marketing site to the spa-and-salon site to simplify the integration and allow server-side processing to happen in the same application.

## Architecture

The subscription system is now fully integrated within the spa-and-salon application:

1. Users visit the pricing page at `/pricing`
2. When they select a subscription plan, the checkout process is handled directly by the spa-and-salon application
3. After checkout, users are redirected to a success page
4. Stripe webhooks are processed by the spa-and-salon application to track subscription status

## Components

### 1. Pricing Page

The pricing page is now available at `/pricing` and displays three subscription tiers:
- Self-Managed ($29.00/month)
- Single Location ($79/month)
- Multi-Location ($295/month)

Each tier includes:
- Descriptive information
- Feature list
- Pricing details
- Free trial indication (if applicable)
- Subscription button

### 2. Subscription Controller

The `SubscriptionController` handles the creation of Stripe checkout sessions:

- Endpoint: `/api/subscriptions/checkout`
- Method: POST
- Parameters:
  - `priceId`: Stripe price ID
  - `firstMonthFree`: Boolean indicating if a 30-day free trial should be applied
  - `successUrl`: URL to redirect after successful checkout
  - `cancelUrl`: URL to redirect if checkout is cancelled

### 3. Success Page

After a successful checkout, users are redirected to the success page at `/success`.

### 4. Stripe Webhook Handler

The `StripeWebhookController` processes webhook events from Stripe to track subscription status, payment success/failure, and other events.

## Free Trial Implementation

The system supports free trials for subscriptions. When a user signs up for a plan with `firstMonthFree: true`, the system will automatically add a 30-day trial period to the subscription.

## Environment Variables

The following environment variables are required:

```
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

## Testing

To test the subscription system:

1. Visit the pricing page at `/pricing`
2. Select a subscription plan
3. Complete the checkout process using Stripe's test card numbers
4. Verify that the subscription is created in the Stripe dashboard
5. Test webhook events using Stripe's webhook testing tools

### Local Webhook Testing with Stripe CLI

For local development and testing of webhooks:

1. Install the [Stripe CLI](https://stripe.com/docs/stripe-cli)
2. Start the webhook forwarding with:
   ```
 stripe listen --forward-to localhost:8000/api/stripe/webhook --load-from-webhooks-api
   ```
3. **Important**: When using the Stripe CLI for local testing, it generates a unique webhook signing secret that's different from your production webhook secret. You must update your local `.env` file with this CLI-generated webhook secret:
   ```
   STRIPE_WEBHOOK_SECRET=whsec_generated_by_stripe_cli
   ```
4. The CLI-generated webhook secret is displayed when you start the `stripe listen` command
5. After updating the webhook secret in your `.env` file, restart your local server for the changes to take effect:
   ```
   php artisan config:clear
   ```
6. Release the email queue with php artisan queue:work
    ```
    php artisan queue:work
    ```
Note that this webhook secret is only for local development. Your production environment should use the webhook secret from your Stripe Dashboard.

## Migrating from Marketing Site

The pricing page has been moved from the marketing site to the spa-and-salon site. If you need to redirect users from the old pricing page URL, add a redirect in the marketing site.

## Subscription Management

All subscription management is handled through Stripe webhooks. The following events are processed:

- `payment_intent.succeeded`: Processes successful payments
- `payment_intent.payment_failed`: Logs failed payment attempts
- `charge.refunded`: Handles refunds

Additional subscription-specific events should be added to the `StripeWebhookController` as needed.
