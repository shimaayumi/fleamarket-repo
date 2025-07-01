<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'ユーザーA',
            'email' => 'userA@example.com',
            'password' => Hash::make('password123'),
        ]);
        User::create([
            'name' => 'ユーザーB',
            'email' => 'userB@example.com',
            'password' => Hash::make('password123'),
        ]);
        User::create([
            'name' => 'ユーザーC',
            'email' => 'userC@example.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
