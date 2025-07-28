<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailCampaign;

class WelcomeEmailCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first company or create a default one
        $company = \App\Models\Company::first();
        
        if (!$company) {
            $this->command->warn('No company found. Skipping welcome email template creation.');
            return;
        }

        if (!EmailCampaign::where('type', 'welcome')->where('company_id', $company->id)->exists()) {
            $welcomeContent = <<<'HTML'
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
        <img src="{app_url}/images/faxtina-logo.png" alt="Faxtina Logo" class="logo">
        <h1>Welcome to Faxtina!</h1>
    </div>

    <div class="content">
        <p>Hello {first_name},</p>

        <p>Thank you for signing up for Faxtina! We're excited to have you on board.</p>

        <p>Your account has been created and you now have admin access to manage your salon's operations.</p>

        {credentials_section}

        <p>To get started, click the button below to complete your onboarding:</p>

        {onboarding_button}

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
        <p>&copy; {current_year} Faxtina. All rights reserved.</p>
        <p>This email was sent to {email}</p>
    </div>
</body>
</html>
HTML;

            EmailCampaign::create([
                'name' => 'Welcome Email Template',
                'type' => 'welcome',
                'subject' => 'Welcome to Faxtina - Your Account Details',
                'content' => $welcomeContent,
                'from_email' => config('mail.from.address', 'noreply@faxt.com'),
                'from_name' => config('mail.from.name', 'Faxtina'),
                'status' => 'active',
                'is_template' => true,
                'is_readonly' => true,
                'company_id' => $company->id,
            ]);

            $this->command->info('Welcome email campaign template created successfully for company: ' . $company->name);
        } else {
            $this->command->info('Welcome email campaign template already exists.');
        }
    }
}
