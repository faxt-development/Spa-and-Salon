<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\User;
use App\Mail\WelcomeNewUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WelcomeEmailService
{
    public function sendWelcomeEmail(User $user, ?string $temporaryPassword = null): void
    {
        // Get the active welcome email campaign
        $campaign = EmailCampaign::where('type', 'welcome')
            ->where('status', 'active')
            ->first();

        if (!$campaign) {
            // Fallback to direct mailable if no campaign found
            Mail::to($user->email)->send(new WelcomeNewUser($user, $temporaryPassword));
            return;
        }

        // Process the campaign content with user variables
        $content = $this->processTemplateContent($campaign->content, $user, $temporaryPassword);
        
        // Send the email
        Mail::to($user->email)
            ->send(new WelcomeNewUser(
                $user,
                $temporaryPassword,
                $campaign->subject,
                $content
            ));
    }

    public function createUserCopy(User $user, EmailCampaign $template): EmailCampaign
    {
        if ($template->is_readonly) {
            $copy = $template->replicate();
            $copy->name = $template->name . ' (Copy)';
            $copy->is_template = false;
            $copy->is_readonly = false;
            $copy->user_id = $user->id;
            $copy->save();
            
            return $copy;
        }
        
        return $template;
    }

    private function processTemplateContent(string $content, User $user, ?string $temporaryPassword): string
    {
        $replacements = [
            '{first_name}' => $user->name,
            '{email}' => $user->email,
            '{temporary_password}' => $temporaryPassword ?? '',
            '{app_url}' => config('app.url'),
            '{current_year}' => date('Y'),
            '{onboarding_url}' => route('onboarding.start'),
            '{credentials_section}' => $temporaryPassword ? $this->buildCredentialsSection($user, $temporaryPassword) : '',
            '{onboarding_button}' => $this->buildOnboardingButton(),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    private function buildCredentialsSection(User $user, string $temporaryPassword): string
    {
        return '<div class="credentials">
            <p><strong>Your login credentials:</strong></p>
            <p>Email: ' . htmlspecialchars($user->email) . '</p>
            <p>Temporary Password: ' . htmlspecialchars($temporaryPassword) . '</p>
            <p>Please change your password after your first login.</p>
        </div>';
    }

    private function buildOnboardingButton(): string
    {
        return '<a href="' . route('onboarding.start') . '" class="button">Complete Your Setup</a>';
    }
}
