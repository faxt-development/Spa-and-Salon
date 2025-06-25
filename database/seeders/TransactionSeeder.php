<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Services\TransactionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the transaction service
        $transactionService = app(TransactionService::class);

        // Get some clients, staff, and rooms
        $clients = Client::take(5)->get();
        $staff = Staff::take(3)->get();
        $rooms = Room::take(2)->get();
        $paymentMethods = PaymentMethod::all();

        if ($clients->isEmpty() || $staff->isEmpty()) {
            $this->command->info('Please seed clients and staff first.');
            return;
        }

        // Create transactions from existing appointments
        $appointments = Appointment::where('status', 'completed')
            ->take(10)
            ->get();

        foreach ($appointments as $appointment) {
            // Create transaction from appointment
            $transaction = $transactionService->createFromAppointment($appointment);

            // Process payment for the transaction
            $transaction->update([
                'payment_method' => $paymentMethods->random()->name,
                'payment_gateway' => 'stripe',
                'card_last_four' => rand(1000, 9999),
                'card_brand' => collect(['visa', 'mastercard', 'amex'])->random(),
                'external_transaction_id' => 'txn_' . uniqid(),
            ]);

            // Add a tip (for some transactions)
            if (rand(0, 1)) {
                $tipAmount = round($transaction->subtotal * (rand(10, 25) / 100), 2);
                $transactionService->addTipLineItem($transaction, $tipAmount);
            }

            // Update totals and complete the transaction
            $transactionService->updateTransactionTotals($transaction);
            $transactionService->completeTransaction($transaction);

            $this->command->info("Created transaction #{$transaction->id} from appointment #{$appointment->id}");
        }

        // Create transactions from existing orders
        $orders = Order::where('status', 'completed')
            ->take(10)
            ->get();

        foreach ($orders as $order) {
            // Create transaction from order
            $transaction = $transactionService->createFromOrder($order);

            // Process payment for the transaction
            $transaction->update([
                'payment_method' => $paymentMethods->random()->name,
                'payment_gateway' => 'stripe',
                'card_last_four' => rand(1000, 9999),
                'card_brand' => collect(['visa', 'mastercard', 'amex'])->random(),
                'external_transaction_id' => 'txn_' . uniqid(),
            ]);

            // Update totals and complete the transaction
            $transactionService->updateTransactionTotals($transaction);
            $transactionService->completeTransaction($transaction);

            $this->command->info("Created transaction #{$transaction->id} from order #{$order->id}");
        }

        // Create some standalone transactions (not linked to appointments or orders)
        for ($i = 0; $i < 5; $i++) {
            $client = $clients->random();
            $staffMember = $staff->random();
            $room = $rooms->random();

            // Create a new transaction
            $transaction = Transaction::create([
                'client_id' => $client->id,
                'staff_id' => $staffMember->id,
                'room_id' => $room->id,
                'transaction_type' => Transaction::TYPE_RETAIL,
                'transaction_date' => now()->subDays(rand(0, 30)),
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'tip_amount' => 0,
                'total_amount' => 0,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => $paymentMethods->random()->name,
            ]);

            // Add some line items
            $serviceAmount = rand(5000, 15000) / 100;
            $productAmount = rand(2000, 5000) / 100;

            // Add service line item
            TransactionLineItem::create([
                'transaction_id' => $transaction->id,
                'item_type' => TransactionLineItem::TYPE_SERVICE,
                'name' => 'Custom Service',
                'description' => 'One-time custom service',
                'quantity' => 1,
                'unit_price' => $serviceAmount,
                'amount' => $serviceAmount,
                'staff_id' => $staffMember->id,
            ]);

            // Add product line item
            TransactionLineItem::create([
                'transaction_id' => $transaction->id,
                'item_type' => TransactionLineItem::TYPE_PRODUCT,
                'name' => 'Retail Product',
                'description' => 'Retail product sale',
                'quantity' => rand(1, 3),
                'unit_price' => $productAmount,
                'amount' => $productAmount * rand(1, 3),
                'staff_id' => $staffMember->id,
            ]);

            // Calculate tax
            $transactionService->calculateAndAddTaxes($transaction);

            // Add tip
            $tipAmount = round(($serviceAmount + $productAmount) * 0.15, 2);
            $transactionService->addTipLineItem($transaction, $tipAmount);

            // Update totals and complete
            $transactionService->updateTransactionTotals($transaction);
            $transactionService->completeTransaction($transaction);

            // Add payment details
            $transaction->update([
                'payment_gateway' => 'stripe',
                'card_last_four' => rand(1000, 9999),
                'card_brand' => collect(['visa', 'mastercard', 'amex'])->random(),
                'external_transaction_id' => 'txn_' . uniqid(),
            ]);

            $this->command->info("Created standalone transaction #{$transaction->id}");
        }

        // Create some refund transactions
        $completedTransactions = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->where('transaction_type', Transaction::TYPE_RETAIL)
            ->take(3)
            ->get();

        foreach ($completedTransactions as $originalTransaction) {
            $refundAmount = round($originalTransaction->total_amount * (rand(25, 100) / 100), 2);
            $reason = 'Customer dissatisfaction';

            $refundTransaction = $transactionService->processRefund(
                $originalTransaction,
                $refundAmount,
                $reason
            );

            $this->command->info("Created refund transaction #{$refundTransaction->id} for transaction #{$originalTransaction->id}");
        }
    }
}
