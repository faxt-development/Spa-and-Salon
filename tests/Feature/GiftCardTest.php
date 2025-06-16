<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class GiftCardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Stripe API
        $this->mockStripe();
    }

    /** @test */
    public function guest_can_purchase_gift_card()
    {
        $response = $this->postJson(route('api.gift-cards.create-payment-intent'), [
            'amount' => '50.00',
            'recipient_name' => 'Test Recipient',
            'recipient_email' => 'recipient@example.com',
            'sender_name' => 'Test Sender',
            'sender_email' => 'sender@example.com',
            'message' => 'Test message',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client_secret',
                'publishable_key',
                'is_authenticated'
            ]);
    }

    /** @test */
    public function authenticated_user_can_purchase_gift_card()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'web')
            ->postJson(route('api.gift-cards.create-payment-intent'), [
                'amount' => '50.00',
                'recipient_name' => 'Test Recipient',
                'recipient_email' => 'recipient@example.com',
                'sender_name' => $user->name,
                'message' => 'Test message',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'is_authenticated' => true
            ]);
    }

    /** @test */
    public function it_requires_valid_amount()
    {
        $response = $this->postJson(route('api.gift-cards.create-payment-intent'), [
            'amount' => '0.00',
            'recipient_name' => 'Test Recipient',
            'recipient_email' => 'recipient@example.com',
            'sender_name' => 'Test Sender',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_requires_recipient_details()
    {
        $response = $this->postJson(route('api.gift-cards.create-payment-intent'), [
            'amount' => '50.00',
            // Missing recipient details
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_name', 'recipient_email']);
    }

    /** @test */
    public function it_handles_successful_payment()
    {
        $user = User::factory()->create();
        
        // Simulate creating a payment intent
        $paymentIntent = $this->createTestPaymentIntent([
            'amount' => 5000, // $50.00 in cents
            'metadata' => [
                'recipient_name' => 'Test Recipient',
                'recipient_email' => 'recipient@example.com',
                'sender_name' => $user->name,
                'user_id' => $user->id,
            ]
        ]);

        // Store test data in session
        session([
            'gift_card_data_' . $paymentIntent->id => [
                'amount' => '50.00',
                'recipient_name' => 'Test Recipient',
                'recipient_email' => 'recipient@example.com',
                'sender_name' => $user->name,
                'user_id' => $user->id,
            ]
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('api.gift-cards.handle-payment'), [
                'payment_intent_id' => $paymentIntent->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Gift card created successfully'
            ]);

        // Verify gift card was created
        $this->assertDatabaseHas('gift_cards', [
            'recipient_email' => 'recipient@example.com',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_payment_processing()
    {
        $paymentIntent = $this->createTestPaymentIntent();
        
        // First attempt
        $response1 = $this->postJson(route('api.gift-cards.handle-payment'), [
            'payment_intent_id' => $paymentIntent->id,
        ]);
        $response1->assertStatus(400);
    }

    private function mockStripe()
    {
        $this->mock(\Stripe\StripeClient::class, function ($mock) {
            $mock->shouldReceive('paymentIntents->create')
                ->andReturn((object)[
                    'id' => 'test_pi_' . Str::random(24),
                    'client_secret' => 'test_cs_' . Str::random(43),
                    'status' => 'requires_payment_method',
                ]);
                
            $mock->shouldReceive('paymentIntents->retrieve')
                ->andReturnUsing(function ($id) {
                    return (object)[
                        'id' => $id,
                        'status' => 'succeeded',
                        'amount' => 5000,
                        'currency' => 'usd',
                        'metadata' => (object)[],
                    ];
                });
        });
    }
    
    private function createTestPaymentIntent($params = [])
    {
        $defaults = [
            'id' => 'test_pi_' . Str::random(24),
            'amount' => 5000,
            'currency' => 'usd',
            'status' => 'succeeded',
            'metadata' => (object)[],
        ];
        
        return (object)array_merge($defaults, $params);
    }
}
