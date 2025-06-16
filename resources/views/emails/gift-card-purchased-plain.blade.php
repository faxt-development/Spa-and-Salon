You've Received a Gift Card! 🎁

{{ $senderName }} has sent you a gift card for {{ config('app.name') }}.

@if($message)

"{{ $message }}"
@endif

Gift Card Details:
- Amount: ${{ number_format($giftCard->amount, 2) }}
- Code: {{ $giftCard->code }}
- Expires: {{ $giftCard->expires_at->format('F j, Y') }}

Redeem your gift card here: {{ route('gift-cards.redeem', ['code' => $giftCard->code]) }}

---
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
