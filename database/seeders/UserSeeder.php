<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin CargoTrace', 'email' => 'admin@cargotrace.test', 'role' => 'admin'],
            ['name' => 'Operator Satu', 'email' => 'operator@cargotrace.test', 'role' => 'operator'],
            ['name' => 'Customer Satu', 'email' => 'customer@cargotrace.test', 'role' => 'customer'],
            ['name' => 'Customer Dua', 'email' => 'customer2@cargotrace.test', 'role' => 'customer'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password', // otomatis di-hash oleh cast 'hashed'
                    'role' => $data['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}