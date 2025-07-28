@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        {{ config('app.name') }}
    @endcomponent
@endslot

# Appointment Confirmed! 🎉

Hello {{ $appointment->client->first_name }},

Your appointment has been confirmed. Here are the details:

**Date:** {{ $appointment->start_time->format('l, F j, Y') }}
**Time:** {{ $appointment->start_time->format('g:i A') }} - {{ $appointment->end_time->format('g:i A') }}

**Services Booked:**
@foreach ($appointment->services as $service)
- **{{ $service->name }}** ({{ $service->duration }} min) - ${{ number_format($service->price, 2) }}
@endforeach

---

**Total Duration:** {{ $appointment->services->sum('duration') }} minutes
**Total Price:** ${{ number_format($appointment->services->sum('price'), 2) }}  
**Staff:** {{ $appointment->staff->full_name }}  
**Location:** {{ $appointment->staff->location->name }}

@if($appointment->notes)
**Your Notes:**  
{{ $appointment->notes }}
@endif

@php
    // Check if this is a guest booking by checking for appointment token
    $appointmentToken = \App\Models\AppointmentToken::where('appointment_id', $appointment->id)
        ->where('email', $appointment->client->email)
        ->where('expires_at', '>', now())
        ->first();
@endphp

@if($appointmentToken)
@component('mail::button', ['url' => route('guest.appointment.view', ['token' => $appointmentToken->token])])
    View Your Appointment Details
@endcomponent

**Save this link:** You can view your appointment details anytime using this link:  
{{ route('guest.appointment.view', ['token' => $appointmentToken->token]) }}

@else
@component('mail::button', ['url' => route('appointments.show', $appointment->id)])
    View Appointment
@endcomponent
@endif

---

**Need to make changes?**
@if($appointmentToken)
- **Reschedule or cancel:** Visit your appointment link above
- **Create account:** Save your preferences and manage all appointments in one place
@else
- **Reschedule or cancel:** Log into your account to manage this appointment
@endif

Please let us know at least 24 hours in advance for any changes.

We look forward to seeing you!

Thanks,  
The {{ config('app.name') }} Team

{{-- Footer --}}
@slot('footer')
    @component('mail::footer')
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        
        **Address:** [Your Business Address]  
        **Phone:** [Your Phone Number]  
        **Email:** [Your Support Email]
        
        [Unsubscribe](#) | [Email Preferences](#)
    @endcomponent
@endslot
@endcomponent
