@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        {{ config('app.name') }}
    @endcomponent
@endslot

# How Was Your Experience? 🌟

Hello {{ $appointment->client->name }},

Thank you for choosing {{ config('app.name') }} for your recent {{ $appointment->service->name }} service. We hope you had a wonderful experience with {{ $appointment->staff->name }}!

We'd love to hear your feedback about your visit. Your review helps us improve our services and lets others know about their experience.

@component('mail::button', ['url' => route('reviews.create', ['appointment' => $appointment->id, 'token' => $appointment->review_token])])
    Leave a Review
@endcomponent

**Service Details:**  
{{ $appointment->service->name }} with {{ $appointment->staff->name }}  
{{ $appointment->scheduled_at->format('l, F j, Y') }} at {{ $appointment->scheduled_at->format('g:i A') }}

If you have any concerns about your visit, please reply to this email and we'll be happy to assist you.

Thank you for your business! We look forward to serving you again soon.

Warm regards,  
The {{ config('app.name') }} Team

{{-- Footer --}}
@slot('footer')
    @component('mail::footer')
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        
        **Address:** [Your Business Address]  
        **Phone:** [Your Phone Number]  
        **Email:** [Your Support Email]
        
        [Unsubscribe](#) | [Email Preferences](#) | [Manage Notifications](#)
    @endcomponent
@endslot
@endcomponent
