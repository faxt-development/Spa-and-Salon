@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        {{ config('app.name') }}
    @endcomponent
@endslot

# Test Email

This is a test email to verify that AWS SES is properly configured.

@component('mail::button', ['url' => config('app.url')])
    Visit Our Website
@endcomponent

Thanks,<br>
{{ config('app.name') }}

{{-- Footer --}}
@slot('footer')
    @component('mail::footer')
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        
        **Unsubscribe** from these emails [here](#).
    @endcomponent
@endslot
@endcomponent
