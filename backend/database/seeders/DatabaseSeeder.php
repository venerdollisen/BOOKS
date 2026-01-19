<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed accounts first (foundational data)
        $this->call(AccountSeeder::class);

        // Seed test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
            'password' => '12345678', // Password will be hashed by the factory
        ]);

        // Seed transactions
        $this->call(TransactionSeeder::class);
    }
}
