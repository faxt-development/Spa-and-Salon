<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EmailCampaign;
use App\Models\Company;
use App\Mail\ClientWelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClientRegistrationController extends Controller
{
    /**
     * Register a new client and send welcome email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Step 1: Validate client data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'company_id' => 'required|exists:companies,id',
            'marketing_consent' => 'boolean',
            'source' => 'nullable|string|max:100',
            'guest_token' => 'nullable|string|exists:appointment_tokens,token',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Step 2: Handle guest-to-client conversion
            $guestAppointments = [];
            if ($request->guest_token) {
                $guestAppointments = $this->handleGuestConversion($request->email, $request->guest_token);
            }

            // Step 3: Create client record
            $client = Client::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'company_id' => $request->company_id,
                'marketing_consent' => $request->marketing_consent ?? false,
                'source' => $request->source ?? 'website',
                'is_guest' => false,
            ]);

            // Step 3: Get welcome email template
            $welcomeTemplate = $this->getWelcomeTemplate($request->company_id);
            
            if ($welcomeTemplate) {
                // Step 4: Send welcome email
                $this->sendWelcomeEmail($client, $welcomeTemplate);
            }

            // Step 6: Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Client registered successfully',
                'data' => [
                    'client' => $client,
                    'welcome_email_sent' => $welcomeTemplate !== null,
                    'converted_appointments' => $guestAppointments,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Client registration failed', [
                'error' => $e->getMessage(),
                'request' => $request->except('password'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get welcome email template with company scoping
     *
     * @param int $companyId
     * @return EmailCampaign|null
     */
    private function getWelcomeTemplate(int $companyId): ?EmailCampaign
    {
        // Step 1: Check for company-specific welcome template
        $companyTemplate = EmailCampaign::where('type', 'welcome')
            ->where('company_id', $companyId)
            ->where('is_template', true)
            ->where('status', 'active')
            ->first();

        if ($companyTemplate) {
            return $companyTemplate;
        }

        // Step 2: Fallback to global welcome template
        $globalTemplate = EmailCampaign::where('type', 'welcome')
            ->whereNull('company_id')
            ->where('is_template', true)
            ->where('status', 'active')
            ->first();

        return $globalTemplate;
    }

    /**
     * Send welcome email to client
     *
     * @param Client $client
     * @param EmailCampaign $template
     * @return void
     */
    private function sendWelcomeEmail(Client $client, EmailCampaign $template): void
    {
        try {
            // Customize template content
            $customizedContent = $this->customizeTemplate($template, $client);
            
            // Send email
            Mail::to($client->email)
                ->send(new ClientWelcomeEmail($client, $customizedContent));

            // Log email sending
            Log::info('Welcome email sent to client', [
                'client_id' => $client->id,
                'email' => $client->email,
                'template_id' => $template->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'client_id' => $client->id,
                'email' => $client->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Customize email template with client data
     *
     * @param EmailCampaign $template
     * @param Client $client
     * @return array
     */
    private function customizeTemplate(EmailCampaign $template, Client $client): array
    {
        $company = Company::find($client->company_id);
        
        // Replace placeholders in content and subject
        $placeholders = [
            '{{client_first_name}}' => $client->first_name,
            '{{client_last_name}}' => $client->last_name,
            '{{client_full_name}}' => $client->full_name,
            '{{client_email}}' => $client->email,
            '{{client_phone}}' => $client->phone,
            '{{company_name}}' => $company->name,
            '{{company_email}}' => $company->email,
            '{{company_phone}}' => $company->phone,
            '{{company_address}}' => $company->address,
            '{{booking_url}}' => config('app.url') . '/book',
            '{{company_url}}' => config('app.url'),
        ];

        $content = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template->content
        );

        $subject = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template->subject
        );

        return [
            'subject' => $subject,
            'content' => $content,
            'from_email' => $template->from_email,
            'from_name' => $template->from_name,
        ];
    }

    /**
     * Handle conversion of guest appointments to registered client
     *
     * @param string $email
     * @param string $guestToken
     * @return array
     */
    private function handleGuestConversion(string $email, string $guestToken): array
    {
        try {
            // Find the appointment token
            $appointmentToken = \App\Models\AppointmentToken::findValidToken($guestToken);
            
            if (!$appointmentToken || $appointmentToken->email !== $email) {
                return [];
            }

            $appointment = $appointmentToken->appointment;
            
            if (!$appointment) {
                return [];
            }

            // Find existing guest client with this email
            $guestClient = \App\Models\Client::where('email', $email)
                ->where('is_guest', true)
                ->first();

            if ($guestClient) {
                // Update the guest client to registered client
                $guestClient->update(['is_guest' => false]);
                
                // Update the appointment to use the updated client record
                $appointment->update(['client_id' => $guestClient->id]);
                
                // Delete the appointment token as it's no longer needed
                $appointmentToken->delete();

                Log::info('Guest appointment converted to registered client', [
                    'appointment_id' => $appointment->id,
                    'client_id' => $guestClient->id,
                    'email' => $email
                ]);

                return [
                    [
                        'appointment_id' => $appointment->id,
                        'appointment_date' => $appointment->start_time->toDateTimeString(),
                        'status' => 'converted'
                    ]
                ];
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Failed to convert guest appointment', [
                'email' => $email,
                'guest_token' => $guestToken,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
