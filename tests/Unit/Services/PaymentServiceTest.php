<?php

namespace Tests\Unit\Services;

use App\Models\GiftCard;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{


    protected $stripeMock;
    protected $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock Stripe client
        $this->stripeMock = Mockery::mock(StripeClient::class);
        $this->app->instance(StripeClient::class, $this->stripeMock);

        // Create the service with the mocked Stripe client
        $this->paymentService = new PaymentService();

        // Mock the Stripe client on the service instance
        $reflection = new \ReflectionClass($this->paymentService);
        $property = $reflection->getProperty('stripe');
        $property->setAccessible(true);
        $property->setValue($this->paymentService, $this->stripeMock);

        // Mock Mail facade
        Mail::fake();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function it_creates_a_payment_intent()
    {
        // Mock the Stripe API response
        $this->stripeMock->shouldReceive('__get')
            ->with('paymentIntents')
            ->andReturnSelf();

        $this->stripeMock->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($params) {
                return $params['amount'] === 5000 &&
                       $params['currency'] === 'usd';
            }))
            ->andReturn((object)[
                'id' => 'test_pi_123',
                'client_secret' => 'test_cs_123',
                'status' => 'requires_payment_method',
            ]);

        $result = $this->paymentService->createGiftCardPaymentIntent(50.00);

        $this->assertTrue($result['success']);
        $this->assertEquals('test_cs_123', $result['client_secret']);
        $this->assertEquals('test_pi_123', $result['payment_intent_id']);
    }

    /** @test */
    public function it_creates_gift_card_for_authenticated_user()
    {
        $user = User::factory()->create();

        $paymentIntent = $this->createMockPaymentIntent([
            'id' => 'pi_123',
            'status' => 'succeeded',
            'metadata' => (object)['user_id' => (string)$user->id],
        ]);

        $giftCardData = [
            'amount' => '50.00',
            'recipient_name' => 'Test Recipient',
            'recipient_email' => 'recipient@example.com',
            'sender_name' => $user->name,
            'user_id' => $user->id,
        ];

        $giftCard = $this->paymentService->handleSuccessfulGiftCardPayment($paymentIntent, $giftCardData);

        $this->assertInstanceOf(GiftCard::class, $giftCard);
        $this->assertEquals($user->id, $giftCard->user_id);
        $this->assertEquals('50.00', $giftCard->amount);
        $this->assertEquals('50.00', $giftCard->balance);
        $this->assertEquals('Test Recipient', $giftCard->recipient_name);
        $this->assertEquals('recipient@example.com', $giftCard->recipient_email);
        $this->assertEquals($user->name, $giftCard->sender_name);
        $this->assertTrue($giftCard->is_active);
    }

    /** @test */
    public function it_creates_gift_card_for_guest()
    {
        $paymentIntent = $this->createMockPaymentIntent([
            'id' => 'pi_123',
            'status' => 'succeeded',
        ]);

        $giftCardData = [
            'amount' => '25.00',
            'recipient_name' => 'Test Recipient',
            'recipient_email' => 'recipient@example.com',
            'sender_name' => 'Guest Sender',
            'sender_email' => 'guest@example.com',
        ];

        $giftCard = $this->paymentService->handleSuccessfulGiftCardPayment($paymentIntent, $giftCardData);

        $this->assertInstanceOf(GiftCard::class, $giftCard);
        $this->assertNull($giftCard->user_id);
        $this->assertEquals('25.00', $giftCard->amount);
        $this->assertEquals('25.00', $giftCard->balance);
        $this->assertEquals('Guest Sender', $giftCard->sender_name);
        $this->assertEquals('guest@example.com', $giftCard->sender_email);
    }

    /** @test */
    public function it_throws_exception_for_failed_payment()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment not completed');

        $paymentIntent = $this->createMockPaymentIntent([
            'id' => 'pi_123',
            'status' => 'requires_payment_method',
        ]);

        $this->paymentService->handleSuccessfulGiftCardPayment($paymentIntent, [
            'amount' => '50.00',
            'recipient_name' => 'Test',
            'recipient_email' => 'test@example.com',
            'sender_name' => 'Sender',
        ]);
    }

    /** @test */
    public function it_requires_mandatory_fields()
    {
        $this->expectException(\InvalidArgumentException::class);

        $paymentIntent = $this->createMockPaymentIntent([
            'status' => 'succeeded',
        ]);

        // Missing required fields
        $this->paymentService->handleSuccessfulGiftCardPayment($paymentIntent, []);
    }

    private function createMockPaymentIntent($overrides = [])
    {
        $defaults = [
            'id' => 'pi_' . uniqid(),
            'status' => 'succeeded',
            'amount' => 5000,
            'currency' => 'usd',
            'metadata' => (object)[],
        ];

        $data = array_merge($defaults, $overrides);

        $paymentIntent = new PaymentIntent($data['id']);
        foreach ($data as $key => $value) {
            $paymentIntent->$key = $value;
        }

        return $paymentIntent;
    }
}
