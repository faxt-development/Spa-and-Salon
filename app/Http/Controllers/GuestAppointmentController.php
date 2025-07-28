<?php

namespace App\Http\Controllers;

use App\Models\AppointmentToken;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;

class GuestAppointmentController extends Controller
{
    /**
     * Display the guest appointment details.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function show($token)
    {
        $appointmentToken = AppointmentToken::findValidToken($token);

        if (!$appointmentToken) {
            return view('guest.appointment-invalid', [
                'message' => 'This appointment link is invalid or has expired.',
                'pageTitle' => 'Appointment Not Found'
            ]);
        }

        $appointment = $appointmentToken->appointment()->with([
            'client',
            'staff',
            'services',
            'staff.location.company'
        ])->first();

        if (!$appointment) {
            return view('guest.appointment-invalid', [
                'message' => 'Appointment not found.',
                'pageTitle' => 'Appointment Not Found'
            ]);
        }

        // Check if the email matches
        if ($appointment->client->email !== $appointmentToken->email) {
            return view('guest.appointment-invalid', [
                'message' => 'This appointment link is not valid for this email address.',
                'pageTitle' => 'Invalid Access'
            ]);
        }

        return view('guest.appointment-confirmation', [
            'appointment' => $appointment,
            'token' => $token,
            'pageTitle' => 'Your Appointment Details'
        ]);
    }

    /**
     * Resend the appointment confirmation email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendConfirmation(Request $request, $token)
    {
        $appointmentToken = AppointmentToken::findValidToken($token);

        if (!$appointmentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.'
            ], 404);
        }

        $appointment = $appointmentToken->appointment()->with([
            'client',
            'staff',
            'services',
            'staff.location.company'
        ])->first();

        if (!$appointment || $appointment->client->email !== $appointmentToken->email) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.'
            ], 404);
        }

        try {
            // Send confirmation email
            event(new \App\Events\AppointmentCreated($appointment));

            Log::info('GuestAppointmentController@resendConfirmation: Confirmation email resent', [
                'appointment_id' => $appointment->id,
                'email' => $appointment->client->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Confirmation email has been resent to ' . $appointment->client->email
            ]);
        } catch (\Exception $e) {
            Log::error('GuestAppointmentController@resendConfirmation: Error sending email', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send confirmation email. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get email address from appointment token
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmail($token)
    {
        $appointmentToken = AppointmentToken::findValidToken($token);

        if (!$appointmentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'email' => $appointmentToken->email
        ]);
    }
}
