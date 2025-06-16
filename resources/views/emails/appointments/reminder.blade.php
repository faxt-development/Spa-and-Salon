@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        {{ config('app.name') }}
    @endcomponent
@endslot

# Reminder: Upcoming Appointment

Hello {{ $appointment->client->name }},

This is a friendly reminder about your upcoming appointment:

**Service:** {{ $appointment->service->name }}  
**Date:** {{ $appointment->scheduled_at->format('l, F j, Y') }}  
**Time:** {{ $appointment->scheduled_at->format('g:i A') }}  
**Duration:** {{ $appointment->duration }} minutes  
**Staff:** {{ $appointment->staff->name }}

@if($appointment->location)
**Location:** {{ $appointment->location }}
@endif

@component('mail::button', ['url' => route('appointments.show', $appointment->id)])
    View Appointment Details
@endcomponent

Need to reschedule or cancel? Please let us know at least 24 hours in advance to avoid any cancellation fees.

We look forward to seeing you soon!

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
