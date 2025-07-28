<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailCampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create the welcome email if it doesn't exist
        if (!\App\Models\EmailCampaign::where('type', 'welcome')->exists()) {
            \App\Models\EmailCampaign::create([
                'name' => 'Welcome Email',
                'type' => 'welcome',
                'subject' => 'Welcome to Faxtina!',
                'content' => '<h1>Welcome to Faxtina!</h1>\n<p>Dear {{first_name}},</p>\n<p>Thank you for joining Faxtina! We\'re excited to have you on board.</p>\n<p>Get started by exploring our services and booking your first appointment.</p>\n<p>If you have any questions, feel free to reach out to our support team.</p>\n<p>Best regards,<br>The Faxtina Team</p>',
                'segment' => 'new_users',
                'status' => 'draft',
                'user_id' => 1, // Assuming user with ID 1 is the admin
            ]);
            
            $this->command->info('Welcome email campaign created successfully!');
        } else {
            $this->command->info('Welcome email campaign already exists.');
        }
    }
}
