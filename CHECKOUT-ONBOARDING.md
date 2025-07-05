# Checkout Onboarding and Notification System

This document outlines the implementation of the checkout onboarding and notification system for Faxtina.

## Overview

When a user completes the checkout process for a subscription (especially with a free trial), the system now:

1. Creates a new user account (or uses an existing one if the email is already registered)
2. Assigns the admin role to the user
3. Creates or updates the subscription record
4. Sends a welcome email to the new user with login credentials and an onboarding link
5. Sends a notification email to administrators about the new free trial registration
6. Guides the user through a step-by-step onboarding process to complete their account setup

## Implementation Details

### 1. Webhook Handler

The core functionality is implemented in the `StripeWebhookController` which now handles the `checkout.session.completed` event from Stripe. When this event is received, the controller:

- Extracts customer information from the checkout session
- Creates a new user account or uses an existing one
- Assigns the admin role to the user
- Creates or updates the subscription record
- Sends welcome email with an onboarding link
- Sends notification emails to administrators

### 2. Email Templates

Two new email templates have been created:

- `welcome-new-user.blade.php`: Sent to new users with their login credentials
- `admin-new-trial-notification.blade.php`: Sent to administrators when a new free trial is registered

### 3. User-Facing Onboarding Flow

A comprehensive onboarding flow has been implemented to guide new users through the setup process after checkout:

1. **Start Page**: Welcomes the user and explains the onboarding process
2. **User Form**: Collects the user's name, email, and password
3. **Company Form**: Collects business information (name, address, contact details)
4. **Feature Tour**: Introduces key features of the application

The onboarding flow is implemented using:

- `OnboardingController`: Handles the onboarding process steps
- `CheckOnboardingStatus` middleware: Ensures users complete onboarding before accessing the dashboard
- Blade templates for each step of the onboarding process
- Session management to track progress through the onboarding flow

### 4. Configuration

A new configuration option has been added to `config/services.php` for admin notification emails:

```php
'admin_notification_emails' => explode(',', env('ADMIN_NOTIFICATION_EMAILS', 'admin@faxtina.com')),
```

This allows multiple admin email addresses to be specified in the `.env` file as a comma-separated list.

## Testing

### Using the TestOnboardingFlow Command

The easiest way to test the implementation is to use the `TestOnboardingFlow` Artisan command:

```bash
# Test with a random email and name
php artisan app:test-onboarding-flow

# Test with a specific email
php artisan app:test-onboarding-flow test@example.com

# Test with a specific email and name
php artisan app:test-onboarding-flow test@example.com "Test User"
```

This command:
- Creates or finds a test plan
- Creates a new user (or uses an existing one if the email is already registered)
- Assigns the admin role to the user
- Creates or updates a subscription record with a 14-day trial period
- Generates a unique session ID for onboarding
- Sends a welcome email to the user with login credentials and an onboarding link
- Sends notification emails to administrators
- Outputs all relevant information to the console (email, password, onboarding URL)

The command is particularly useful for testing the entire onboarding flow without having to go through the Stripe checkout process.

### Testing with Stripe Webhooks

To test the implementation with actual Stripe webhooks:

1. Make sure your Stripe webhook is properly configured to send `checkout.session.completed` events to your application
2. Set up the `ADMIN_NOTIFICATION_EMAILS` environment variable in your `.env` file
3. Complete a checkout process with a subscription
4. Check that:
   - A new user is created in the database
   - The user has the admin role
   - The subscription is recorded in the database
   - The welcome email is sent to the user with an onboarding link
   - The notification email is sent to the administrators
   - When the user clicks the onboarding link, they are guided through the onboarding process
   - After completing onboarding, the user is redirected to the dashboard

### Testing the Onboarding Flow UI

To test just the onboarding flow UI without going through the Stripe checkout or using the command:

1. Use the test route `/test-onboarding` which simulates a session ID from Stripe
2. This will redirect you to the onboarding start page
3. Complete each step of the onboarding process
4. Verify that after completing all steps, you are redirected to the dashboard
5. Check that the `onboarding_completed` field is set to `true` in the user's record

### Local Testing

For local testing, you can use Stripe's webhook testing tools:

1. Install the Stripe CLI: https://stripe.com/docs/stripe-cli
2. Forward webhook events to your local server:
   ```
   stripe listen --forward-to localhost:8000/api/stripe/webhook --load-from-webhooks-api
   ```
3. Trigger a test checkout.session.completed event:
   ```
   stripe trigger checkout.session.completed
   ```

## Troubleshooting

If the onboarding process is not working as expected:

1. Check the Laravel logs for any errors
2. Verify that the Stripe webhook is properly configured and sending events
3. Ensure that the `ADMIN_NOTIFICATION_EMAILS` environment variable is set correctly
4. Check that the email configuration is working properly
5. For email issues:
   - Ensure the mail configuration in `.env` is correct
   - If using the 'log' mail driver, check that the log channel is properly configured in `config/mail.php`
   - Note that emails sent with `Mail::queue()` or through mailables implementing `ShouldQueue` won't appear immediately in logs
   - Use `Mail::mailer('log')` to force immediate sending through the log driver
   -- release the email queue with php artisan queue:work
   

## Future Improvements

Potential future improvements to the onboarding process:

1. Add more detailed user information collection during checkout
2. Implement a more comprehensive onboarding flow with guided setup steps
3. Add SMS notifications for new registrations
4. Create a dashboard for administrators to view and manage new registrations
