<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create dummy payment methods for all users
        $users = User::all();
        $cardTypes = ['visa', 'mastercard', 'amex'];

        foreach ($users as $user) {
            // Create 1-2 payment methods for each user
            $numCards = rand(1, 2);
            for ($i = 0; $i < $numCards; $i++) {
                PaymentMethod::create([
                    'user_id' => $user->id,
                    'card_type' => $cardTypes[array_rand($cardTypes)],
                    'last_four' => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'expiry_date' => rand(1, 12) . '/' . rand(25, 30),
                    'is_default' => $i === 0, // First card is default
                ]);
            }
        }

        // 2. Create payments for existing invoices
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            // Randomly decide if the invoice is paid or pending
            $status = rand(0, 1) ? 'paid' : 'pending';

            if ($status === 'paid') {
                // Get a payment method for the user associated with the invoice's subscription
                $user = $invoice->subscription->patient->user;
                $paymentMethod = $user->paymentMethods()->first();

                if ($paymentMethod) {
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'payment_method_id' => $paymentMethod->id,
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'amount' => $invoice->total_amount,
                        'currency' => 'USD',
                        'status' => 'completed',
                        'payment_date' => Carbon::now()->subDays(rand(1, 30)),
                    ]);

                    // Update invoice status
                    $invoice->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod->card_type . ' ending in ' . $paymentMethod->last_four,
                        'payment_date' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
