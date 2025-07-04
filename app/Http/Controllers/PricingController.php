<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display the pricing page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get the selected priceId from the querystring if it exists
        $selectedPriceId = $request->query('priceId');
        
        $pricingTiers = [
            [
                'name' => 'Self-Managed',
                'price' => '$49.95',
                'period' => '/month',
                'description' => 'Perfect for individual practitioners',
                'features' => [
                    'Unlimited Appointments',
                    'Client Management',
                    'Staff Scheduling',
                    'Inventory Tracking',
                    'Basic Reporting',
                    'Email Support'
                ],
                'cta' => 'Get Started',
                'highlight' => false,
                'firstMonthFree' => true,
                'priceId' => env('price_self_managed', 'price_1RfpQbJmhER0XpDiOsfRwv4X'),
                'image' => '/self-managed-tier.png'
            ],
            [
                'name' => 'Single Location',
                'price' => '$150',
                'period' => '/month',
                'description' => 'Ideal for small to medium salons',
                'features' => [
                    'Unlimited Appointments',
                    'Client Management',
                    'Staff Scheduling',
                    'Inventory Tracking',
                    'Basic Reporting',
                    'Email Support',
                    'Priority Email Support',
                    '1-hour response time'
                ],
                'cta' => 'Start Free Trial',
                'highlight' => true,
                'firstMonthFree' => true,
                'priceId' => env('price_single_location', 'price_1RfpRkJmhER0XpDi4cUCBw0O'),
                'image' => '/single-location-tier.png'
            ],
            [
                'name' => 'Multi-Location',
                'price' => '$295',
                'period' => '/month',
                'description' => 'For growing salon & spa chains',
                'features' => [
                    'Unlimited Appointments',
                    'Client Management',
                    'Staff Scheduling',
                    'Inventory Tracking',
                    'Basic Reporting',
                    'Email Support',
                    'Priority Phone & Email Support',
                    '30-minute response time',
                    'Dedicated Account Manager'
                ],
                'cta' => 'Start Free Trial',
                'highlight' => false,
                'firstMonthFree' => true,
                'priceId' => env('price_multi_location', 'price_1RfpS9JmhER0XpDi5rrviIO5'),
                'image' => '/multi-location-tier.png'
            ]
        ];

        // Update the highlight property based on the selectedPriceId
        if ($selectedPriceId) {
            foreach ($pricingTiers as $key => $tier) {
                // If this tier matches the selected priceId (either the env variable name or the actual price ID)
                if ($selectedPriceId === 'price_' . strtolower(str_replace(' ', '_', $tier['name'])) || 
                    $selectedPriceId === $tier['priceId']) {
                    // Set this tier as highlighted
                    $pricingTiers[$key]['highlight'] = true;
                } else {
                    // Ensure other tiers are not highlighted
                    $pricingTiers[$key]['highlight'] = false;
                }
            }
        }
        
        return view('pricing', [
            'pricingTiers' => $pricingTiers,
            'selectedPriceId' => $selectedPriceId
        ]);
    }

    /**
     * Create a checkout session for the selected pricing tier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCheckoutSession(Request $request)
    {
        // This functionality is already implemented in the SubscriptionController
        // We'll use that endpoint for API calls
        return redirect()->route('subscriptions.checkout');
    }
}
