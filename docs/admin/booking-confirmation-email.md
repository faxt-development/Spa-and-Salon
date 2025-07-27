# Booking Confirmation Email & Marketing Tracking

This document describes the booking confirmation email system and marketing tracking functionality that automatically sends confirmation emails to clients after successful appointment bookings.

## Overview

When a client successfully books an appointment through the guest booking system, the system automatically:

1. **Sends a confirmation email** to the client with appointment details
2. **Tracks the email as sent** for marketing analytics
3. **Adds the client to marketing lists** if they have opted in to marketing communications

## How It Works

### Email Sending Process

The booking confirmation email is triggered immediately after a successful appointment creation through the guest booking interface. The process follows these steps:

1. **Appointment Creation**: When a guest booking is successfully completed
2. **Event Dispatch**: The system dispatches an `AppointmentCreated` event
3. **Email Trigger**: The `SendAppointmentConfirmation` listener processes the event
4. **Email Delivery**: A confirmation email is sent to the client's email address
5. **Marketing Tracking**: The email is recorded in the marketing system
6. **Marketing List Addition**: Client is added to marketing campaigns if consented

### Email Content

The confirmation email includes:
- **Appointment Details**: Service name, date, time, and duration
- **Staff Information**: Name of the assigned staff member
- **Location Details**: Salon/spa address and contact information
- **View Appointment Button**: Direct link to view appointment details
- **Contact Information**: How to reach the salon for changes or questions

### Marketing Tracking

Each confirmation email is tracked in the marketing system with the following data:

- **Email Campaign**: "Booking Confirmation Emails" (system campaign)
- **Recipient Information**: Client email and ID
- **Send Status**: Marked as "sent" with timestamp
- **Tracking Token**: Unique identifier for analytics

### Marketing List Subscription

Clients are automatically added to the "General Marketing" campaign if:
- They have `marketing_consent = true` in their client profile
- They are not already subscribed to the campaign
- The subscription is recorded with "subscribed" status

## Configuration

### Email Campaign Setup

The system automatically creates and manages two email campaigns:

1. **Booking Confirmation Emails** (Transactional)
   - Type: Transactional
   - Purpose: Track booking confirmation emails
   - Status: Always active

2. **General Marketing** (Marketing)
   - Type: Marketing
   - Purpose: General marketing communications
   - Status: Active for clients with marketing consent

### Client Marketing Consent

Marketing consent is managed through the client record:
- **Field**: `marketing_consent` (boolean)
- **Default**: false (opt-in required)
- **Location**: Client profile in admin interface
- **Effect**: Determines marketing list inclusion

## Monitoring & Analytics

### Email Tracking

Monitor booking confirmation emails through:
- **Email Campaign Reports**: View sent confirmation emails
- **Client Communication History**: See emails sent to specific clients
- **Delivery Status**: Track successful deliveries vs. failures

### Marketing List Growth

Track marketing list growth through:
- **Email Campaign Subscribers**: View subscriber counts
- **Client Marketing Status**: See which clients are opted in
- **Subscription Sources**: Identify clients added via booking confirmations

## Troubleshooting

### Common Issues

**Issue**: Confirmation emails not being sent
- **Check**: Ensure `AppointmentCreated` event is being dispatched
- **Check**: Verify email queue is running (`php artisan queue:work`)
- **Check**: Confirm email configuration is correct

**Issue**: Clients not being added to marketing lists
- **Check**: Verify client has `marketing_consent = true`
- **Check**: Ensure email campaigns exist in system
- **Check**: Look for duplicate email addresses

**Issue**: Email tracking not working
- **Check**: Verify EmailCampaign and EmailRecipient models are accessible
- **Check**: Check Laravel logs for any errors
- **Check**: Ensure database migrations are up to date

### Log Files

Relevant log entries can be found in:
- **Laravel Logs**: `storage/logs/laravel.log`
- **Email Logs**: Look for "Appointment confirmation email sent" entries
- **Marketing Logs**: Look for "Client added to marketing list" entries

## Customization

### Email Template

The confirmation email template is located at:
`resources/views/emails/appointments/confirmation.blade.php`

### Email Subject

The email subject can be customized in:
`app/Mail/AppointmentConfirmation.php` in the `envelope()` method

### Marketing Campaign Names

Campaign names can be customized in:
`app/Listeners/SendAppointmentConfirmation.php` in the tracking methods

## Best Practices

### For Staff

1. **Always verify client email addresses** during booking
2. **Confirm marketing consent** with clients during registration
3. **Monitor email delivery** through the admin interface
4. **Update client preferences** when requested

### For Administrators

1. **Regular review** of email campaign performance
2. **Monitor unsubscribe rates** and adjust strategies
3. **Test email delivery** periodically
4. **Keep email templates** updated with current branding
5. **Review marketing consent** compliance regularly

## Technical Details

### Event Flow

```
Guest Booking → BookingService → AppointmentCreated Event → 
SendAppointmentConfirmation Listener → Email + Marketing Tracking
```

### Database Tables Used

- **appointments**: Stores appointment details
- **clients**: Stores client information and marketing consent
- **email_campaigns**: Tracks email campaigns
- **email_recipients**: Tracks individual email sends and subscriptions

### Queue Configuration

The email sending is queued for better performance:
- **Queue Name**: `emails`
- **Connection**: Configured in `config/queue.php`
- **Processing**: Requires queue worker to be running

## Support

For technical issues or questions about the booking confirmation email system:

1. Check this documentation first
2. Review Laravel logs for error details
3. Verify all required services are running
4. Contact system administrator if issues persist
