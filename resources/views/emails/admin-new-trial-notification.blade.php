<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Free Trial Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
        }
        .user-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .subscription-details {
            background-color: #f0f7ff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Free Trial Registration</h1>
    </div>

    <div class="content">
        <p>Hello Admin,</p>
        
        <p>A new user has registered for a free trial of Faxtina.</p>
        
        <div class="user-details">
            <h3>User Details:</h3>
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Phone:</strong> {{ $user->phone_number ?? 'Not provided' }}</p>
            <p><strong>Registered at:</strong> {{ $user->created_at->format('F j, Y, g:i a') }}</p>
        </div>
        
        <div class="subscription-details">
            <h3>Subscription Details:</h3>
            <p><strong>Plan:</strong> {{ $subscription->plan->name ?? 'Unknown Plan' }}</p>
            <p><strong>Status:</strong> {{ $subscription->stripe_status }}</p>
            <p><strong>Trial Ends:</strong> {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('F j, Y') : 'No trial' }}</p>
            <p><strong>Stripe ID:</strong> {{ $subscription->stripe_id }}</p>
        </div>
        
        <p>The user has been set up with admin access to their new account and has received a welcome email with login instructions.</p>
        
        <a href="{{ config('app.url') }}/admin/users/{{ $user->id }}" class="button">View User Details</a>
        
        <p>Best regards,<br>The Faxtina System</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Faxtina. All rights reserved.</p>
    </div>
</body>
</html>
