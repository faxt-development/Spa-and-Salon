<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\Request;

class GiftCardWebController extends Controller
{
    /**
     * Display the gift card purchase form.
     *
     * 
     */
    public function purchaseForm()
    {
        return view('gift-cards.purchase');
    }

    /**
     * Display the user's gift card purchase history.
     *
     * 
     */
    public function historyUser()
    {
        $giftCards = auth()->user()->giftCards()
            ->latest()
            ->paginate(10);

        return view('gift-cards.historyuser', [
            'giftCards' => $giftCards
        ]);
    }

     /**
     * Display the business gift card purchase history.
     *
     * 
     */
    public function history()
    {
        $giftCards = \App\Models\GiftCard::where('amount','<=','balance')
            ->latest()
            ->paginate(10);

        return view('gift-cards.historyall', [
            'giftCards' => $giftCards
        ]);
    }
}
