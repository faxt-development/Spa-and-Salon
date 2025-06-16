@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    # You've Received a Gift Card! 🎁

    {{ $senderName }} has sent you a gift card for {{ config('app.name') }}.

    @if($message)
        > *"{{ $message }}"*
    @endif

    **Gift Card Details:**
    - **Amount:** ${{ number_format($giftCard->amount, 2) }}
    - **Code:** {{ $giftCard->code }}
    - **Expires:** {{ $giftCard->expires_at->format('F j, Y') }}

    @component('mail::button', ['url' => route('gift-cards.redeem', ['code' => $giftCard->code])])
        Redeem Your Gift Card
    @endcomponent

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
