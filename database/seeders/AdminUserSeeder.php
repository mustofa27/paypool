<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'mustofaahmad@poltera.ac.id'],
            [
                'name' => 'Mustofa Ahmad',
                'email' => 'mustofaahmad@poltera.ac.id',
                'password' => Hash::make('ZXCasd123!@#'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: mustofaahmad@poltera.ac.id');
        $this->command->info('Password: ZXCasd123!@#');
    }
}
