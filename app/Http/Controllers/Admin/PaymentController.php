<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show the payment methods configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function methods()
    {
        $stripeEnabled = !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'));
        $stripeConnected = false;
        
        // Check if Stripe is properly connected
        if ($stripeEnabled) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $account = $stripe->account->retrieve();
                $stripeConnected = !empty($account->id);
            } catch (ApiErrorException $e) {
                Log::error('Stripe connection error: ' . $e->getMessage());
            }
        }

        return view('admin.payments.methods', [
            'stripeEnabled' => $stripeEnabled,
            'stripeConnected' => $stripeConnected,
            'pageTitle' => 'Payment Methods Configuration',
        ]);
    }

    /**
     * Show the pricing rules configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function pricing()
    {
        return view('admin.payments.pricing', [
            'pageTitle' => 'Pricing Rules Configuration',
        ]);
    }

    /**
     * Update pricing rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePricing(Request $request)
    {
        // Validate and save pricing rules
        $request->validate([
            'pricing_rules' => 'nullable|array',
        ]);

        // Save pricing rules to settings or database
        // Implementation depends on how settings are stored in the application

        return redirect()->route('admin.payments.pricing')
            ->with('success', 'Pricing rules updated successfully.');
    }

    /**
     * Show the tax settings configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function tax()
    {
        return view('admin.payments.tax', [
            'pageTitle' => 'Tax Settings Configuration',
        ]);
    }

    /**
     * Update tax settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTax(Request $request)
    {
        // Validate and save tax settings
        $request->validate([
            'tax_enabled' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_name' => 'nullable|string|max:255',
        ]);

        // Save tax settings to settings or database
        // Implementation depends on how settings are stored in the application

        return redirect()->route('admin.payments.tax')
            ->with('success', 'Tax settings updated successfully.');
    }
}
