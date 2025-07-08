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
//two sets of pricing tiers, one for test and one for production
        if (app()->environment('production')) {
            $pricingTiers = [
                [
                    'name' => 'Self-Managed',
                    'price' => '$29.00',
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
                    'priceId' => env('price_self_managed'),
                    'image' => '/self-managed-tier.png'
                ],
                [
                    'name' => 'Single Location',
                    'price' => '$79.00',
                    'period' => '/month',
                    'description' => 'Ideal for small to medium salons',
                    'features' => [
                        'Unlimited Appointments',
                        'Client Management',
                        'Staff Scheduling',
                        'Inventory Tracking',
                        'Basic Reporting',
                        'Phone Support'
                    ],
                    'cta' => 'Get Started',
                    'highlight' => true,
                    'firstMonthFree' => true,
                    'priceId' => env('price_single_location'),
                    'image' => '/single-location-tier.png'
                ],
                [
                    'name' => 'Multi-Location',
                    'price' => '$295.00',
                    'period' => '/month',
                    'description' => 'Designed for chains and franchises',
                    'features' => [
                        'Unlimited Appointments',
                        'Client Management',
                        'Staff Scheduling',
                        'Inventory Tracking',
                        'Advanced Reporting',
                        'Phone Support',
                        'Dedicated Support'
                    ],
                    'cta' => 'Get Started',
                    'highlight' => false,
                    'firstMonthFree' => true,
                    'priceId' => env('price_multi_location'),
                    'image' => '/multi-location-tier.png'
                ]
            ];
        } else {
            //dev
            $pricingTiers = [
                [
                    'name' => 'Self-Managed',
                    'price' => '$29.00',
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
                    'priceId' => 'price_1RgT3OJmhER0XpDiBNKUELdh',
                    'image' => '/self-managed-tier.png'
                ],
                [
                    'name' => 'Single Location',
                    'price' => '$79.00',
                    'period' => '/month',
                    'description' => 'Ideal for small to medium salons',
                    'features' => [
                        'Unlimited Appointments',
                        'Client Management',
                        'Staff Scheduling',
                        'Inventory Tracking',
                        'Basic Reporting',
                        'Phone Support'
                    ],
                    'cta' => 'Get Started',
                    'highlight' => true,
                    'firstMonthFree' => true,
                    'priceId' => 'price_1RgT4HJmhER0XpDikdthr2xp',
                    'image' => '/single-location-tier.png'
                ],
                [
                    'name' => 'Multi-Location',
                    'price' => '$295.00',
                    'period' => '/month',
                    'description' => 'Designed for chains and franchises',
                    'features' => [
                        'Unlimited Appointments',
                        'Client Management',
                        'Staff Scheduling',
                        'Inventory Tracking',
                        'Advanced Reporting',
                        'Phone Support',
                        'Dedicated Support'
                    ],
                    'cta' => 'Get Started',
                    'highlight' => false,
                    'firstMonthFree' => true,
                    'priceId' => 'price_1RgT57JmhER0XpDiySm2HC0h',
                    'image' => '/multi-location-tier.png'
                ]
            ];
        }


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
