<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Deposit;

class DepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deposits = [
            [
                'trading_account' => '4563768',
                'email' => 'hasnat@gmail.com',
                'first_name' => 'Hasnat',
                'last_name' => 'Saimun',
                'amount' => 57575.00,
                'net_amount' => 75676.00,
                'currency' => 'USDT',
                'status' => 'approved',
                'payment_gateway' => 'Bank Payment',
                'payment_id' => '85673443647',
                'last_contact' => '2024-10-10',
                'offer' => 'Standard',
                'crypto_address' => 'Binance',
                'reference_code' => '15986542368',
                'lead_source' => 'Ben',
                'account_manager' => 'Admin',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)
            ],
            [
                'trading_account' => '7891234',
                'email' => 'john.doe@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'amount' => 25000.00,
                'net_amount' => 24500.00,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_gateway' => 'Credit Card',
                'payment_id' => '98765432101',
                'last_contact' => '2024-10-15',
                'offer' => 'Premium',
                'crypto_address' => null,
                'reference_code' => '78945612300',
                'lead_source' => 'Website',
                'account_manager' => 'Sarah',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3)
            ],
            [
                'trading_account' => '5647382',
                'email' => 'alice.smith@example.com',
                'first_name' => 'Alice',
                'last_name' => 'Smith',
                'amount' => 100000.00,
                'net_amount' => 99500.00,
                'currency' => 'EUR',
                'status' => 'approved',
                'payment_gateway' => 'Wire Transfer',
                'payment_id' => '11223344556',
                'last_contact' => '2024-10-20',
                'offer' => 'VIP',
                'crypto_address' => 'Coinbase',
                'reference_code' => '33445566778',
                'lead_source' => 'Referral',
                'account_manager' => 'Michael',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1)
            ]
        ];

        foreach ($deposits as $deposit) {
            Deposit::create($deposit);
        }
    }
}
