New Contact Form Submission

{{ str_repeat('=', 60) }}

Name: {{ $formData['name'] }}

Email: {{ $formData['email'] }}

Subject: {{ $formData['subject'] }}

Message:
{{ str_repeat('-', 60) }}
{{ $formData['message'] }}
{{ str_repeat('-', 60) }}

This message was sent from the contact form on {{ config('app.name') }}.

{{ str_repeat('=', 60) }}
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
