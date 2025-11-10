<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('filament.admin.email', 'admin@example.com');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('filament.admin.name', 'Administrator'),
                'password' => Hash::make(config('filament.admin.password', 'password')),
            ],
        );
    }
}
