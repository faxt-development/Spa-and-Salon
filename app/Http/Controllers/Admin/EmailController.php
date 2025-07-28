<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EmailCampaign;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailController extends Controller
{
    /**
     * Show the welcome email configuration page.
     * these welcome emails are sent to Spa CLIENTS
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        // Get the current user's primary company
        $company = auth()->user()->primaryCompany();

        // Get any existing welcome email templates for this company
        $welcomeTemplates = EmailCampaign::where('type', 'welcome')
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the welcome template to initialize from (company-specific or global)
        $welcomeTemplate = EmailCampaign::where('type', 'welcome')
            ->where(function($query) use ($company) {
                $query->where('company_id', $company->id)
                      ->orWhere('is_template', true);
            })
            ->orderBy('company_id', 'desc') // Prefer company-specific template
            ->first();

        return view('admin.email.welcome', [
            'welcomeTemplates' => $welcomeTemplates,
            'welcomeTemplate' => $welcomeTemplate,
        ]);
    }

    /**
     * Show the appointment reminders configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function reminders()
    {
        // Get any existing reminder templates
        $reminderTemplates = EmailCampaign::where('type', 'reminder')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get reminder settings
        $reminderSettings = [
            'days_before' => config('app.reminder_days_before', 1),
            'send_time' => config('app.reminder_send_time', '09:00'),
            'enabled' => config('app.reminders_enabled', true),
        ];

        return view('admin.email.reminders', [
            'reminderTemplates' => $reminderTemplates,
            'reminderSettings' => $reminderSettings,
        ]);
    }

    /**
     * Show the marketing campaigns configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function campaigns()
    {
        // Get recent campaigns
        $campaigns = EmailCampaign::where('type', 'marketing')
            ->orderBy('created_at', 'desc')
            ->withCount('recipients')
            ->paginate(10);

        // Get client segments for targeting
        $segments = [
            ['id' => 'all', 'name' => 'All Clients'],
            ['id' => 'recent', 'name' => 'Recent Clients (last 30 days)'],
            ['id' => 'inactive', 'name' => 'Inactive Clients (60+ days)'],
            ['id' => 'high_value', 'name' => 'High-Value Clients ($500+ this year)'],
            ['id' => 'birthday_this_month', 'name' => 'Birthdays This Month'],
            ['id' => 'no_appointments', 'name' => 'No Appointments Yet'],
        ];

        return view('admin.email.campaigns', [
            'campaigns' => $campaigns,
            'segments' => $segments,
        ]);
    }

    /**
     * Store a new welcome email template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeWelcome(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
        ]);

        // Get the current user's primary company
        $company = auth()->user()->primaryCompany();

        EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'type' => 'welcome',
            'status' => 'active',
            'is_template' => false,
            'is_readonly' => false,
            'user_id' => auth()->id(),
            'company_id' => $company->id,
        ]);

        return redirect()->route('admin.email.welcome')
            ->with('success', 'Welcome email template created successfully.');
    }

    public function copyTemplate(EmailCampaign $campaign)
    {
        if ($campaign->is_readonly) {
            $copy = $campaign->replicate();
            $copy->name = $campaign->name . ' (Copy)';
            $copy->is_template = false;
            $copy->is_readonly = false;
            $copy->user_id = auth()->id();
            $copy->save();

            return redirect()->route('admin.email.welcome')
                ->with('success', 'Template copied successfully. You can now edit the copy.');
        }

        return redirect()->route('admin.email.welcome')
            ->with('error', 'This template cannot be copied.');
    }

    /**
     * Store a new reminder email template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReminder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'required|string|max:255',
        ]);

        EmailCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'type' => 'reminder',
            'status' => 'active',
        ]);

        return redirect()->route('admin.email.reminders')
            ->with('success', 'Reminder email template created successfully.');
    }

    /**
     * Update reminder settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateReminderSettings(Request $request)
    {
        $validated = $request->validate([
            'days_before' => 'required|integer|min:1|max:7',
            'send_time' => 'required|string',
            'enabled' => 'boolean',
        ]);

        // In a real application, we would update these settings in the database
        // For now, we'll just redirect with a success message

        return redirect()->route('admin.email.reminders')
            ->with('success', 'Reminder settings updated successfully.');
    }
}
