@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        {{ config('app.name') }}
    @endcomponent
@endslot

# Appointment Confirmed! 🎉

Hello {{ $appointment->client->name }},

Your appointment has been confirmed. Here are the details:

**Service:** {{ $appointment->service->name }}  
**Date:** {{ $appointment->scheduled_at->format('l, F j, Y') }}  
**Time:** {{ $appointment->scheduled_at->format('g:i A') }}  
**Duration:** {{ $appointment->duration }} minutes  
**Staff:** {{ $appointment->staff->name }}

@if($appointment->notes)
**Your Notes:**  
{{ $appointment->notes }}
@endif

@component('mail::button', ['url' => route('appointments.show', $appointment->id)])
    View Appointment
@endcomponent

Need to reschedule or cancel? Please let us know at least 24 hours in advance.

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
