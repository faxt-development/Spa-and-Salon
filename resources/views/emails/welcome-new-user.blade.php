<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Faxtina</title>
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
        .logo {
            max-width: 200px;
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
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .credentials {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/faxtina-logo.png') }}" alt="Faxtina Logo" class="logo">
        <h1>Welcome to Faxtina!</h1>
    </div>

    <div class="content">
        <p>Hello {{ $user->name }},</p>

        <p>Thank you for signing up for Faxtina! We're excited to have you on board.</p>

        <p>Your account has been created and you now have admin access to manage your salon's operations.</p>

        @if($temporaryPassword)
        <div class="credentials">
            <p><strong>Your login credentials:</strong></p>
            <p>Email: {{ $user->email }}</p>
            <p>Temporary Password: {{ $temporaryPassword }}</p>
            <p>Please change your password after your first login.</p>
        </div>
        @endif

        <p>To get started, click the button below to complete your onboarding:</p>

        @if($onboardingUrl)
        <a href="{{ $onboardingUrl }}" class="button">Complete Your Setup</a>
        @else
        <a href="{{ config('app.url') }}/login" class="button">Access Your Dashboard</a>
        @endif

        <p>Here's what you can do with your Faxtina account:</p>
        <ul>
            <li>Manage your salon's appointments</li>
            <li>Track inventory and sales</li>
            <li>Manage staff and clients</li>
            <li>Access reports and analytics</li>
        </ul>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team at info@faxt.com.</p>

        <p>Best regards,<br>The Faxtina Team</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Faxtina. All rights reserved.</p>
        <p>This email was sent to {{ $user->email }}</p>
    </div>
</body>
</html>
