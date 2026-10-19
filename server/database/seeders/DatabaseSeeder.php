<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $customer = Customer::updateOrCreate(
                ['email' => 'customer@anteraja.test'],
                [
                    'company_name' => 'Apotek Sehat',
                    'contact_name' => 'Customer Pharma',
                    'company_type' => 'pharmacy',
                    'whatsapp_number' => '6281234567890',
                    'whatsapp_opt_in' => false,
                    'address' => 'Jakarta, Indonesia',
                    'is_active' => true,
                ],
            );

            $users = [
                [
                    'name' => 'Admin Anteraja',
                    'email' => 'admin@anteraja.test',
                    'role' => 'admin',
                    'customer_id' => null,
                ],
                [
                    'name' => 'Checker Anteraja',
                    'email' => 'checker@anteraja.test',
                    'role' => 'checker',
                    'customer_id' => null,
                ],
                [
                    'name' => 'Kurir Anteraja',
                    'email' => 'courier@anteraja.test',
                    'role' => 'courier',
                    'customer_id' => null,
                ],
                [
                    'name' => 'Customer Pharma',
                    'email' => 'customer@anteraja.test',
                    'role' => 'customer',
                    'customer_id' => $customer->id,
                ],
            ];

            foreach ($users as $attributes) {
                $user = User::updateOrCreate(
                    ['email' => $attributes['email']],
                    [
                        'name' => $attributes['name'],
                        'password' => 'password',
                    ],
                );

                $user->forceFill([
                    'role' => $attributes['role'],
                    'customer_id' => $attributes['customer_id'],
                    'is_active' => true,
                ])->save();
            }
        });
    }
}
