<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_one_account_for_each_role_and_is_repeatable(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@anteraja.test',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'checker@anteraja.test',
            'role' => 'checker',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'courier@anteraja.test',
            'role' => 'courier',
        ]);

        $customerUser = User::where('email', 'customer@anteraja.test')->firstOrFail();
        $this->assertSame('customer', $customerUser->role);
        $this->assertNotNull($customerUser->customer_id);
        $this->assertTrue(Hash::check('password', $customerUser->password));
    }
}
