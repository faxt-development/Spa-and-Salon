@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Email Content --}}
    {!! $content !!}

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <div style="font-size: 12px; color: #666; text-align: center; padding: 10px 0;">
                <p>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
                <p>
                    <a href="{{ $unsubscribeUrl }}" style="color: #666; text-decoration: underline;">Unsubscribe</a>
                    |
                    <a href="{{ $preferencesUrl }}" style="color: #666; text-decoration: underline;">Email Preferences</a>
                </p>
                <p style="font-size: 10px; color: #999; margin-top: 10px;">
                    If you're having trouble clicking the links, copy and paste the URLs below into your web browser:
                    <br>
                    Unsubscribe: {{ $unsubscribeUrl }}
                    <br>
                    Email Preferences: {{ $preferencesUrl }}
                </p>
                <p style="font-size: 10px; color: #999; margin-top: 10px;">
                    {{ config('app.name') }}, {{ config('app.address') }}
                </p>
            </div>
        @endcomponent
    @endslot
@endcomponent
