<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@selforder.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        // Create default kasir user
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@selforder.com'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $kasir->assignRole('kasir');

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@selforder.com / password');
        $this->command->info('Kasir: kasir@selforder.com / password');
    }
}
