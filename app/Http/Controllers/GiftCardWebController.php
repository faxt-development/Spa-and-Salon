<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GiftCardWebController extends Controller
{
    /**
     * Display the gift card purchase form.
     *
     * @return \Inertia\Response
     */
    public function purchaseForm()
    {
        return view('gift-cards.purchase');
    }

    /**
     * Display the user's gift card purchase history.
     *
     * @return \Inertia\Response
     */
    public function history()
    {
        $giftCards = auth()->user()->giftCards()
            ->latest()
            ->paginate(10);

        return view('gift-cards.history', [
            'giftCards' => $giftCards
        ]);
    }
}
