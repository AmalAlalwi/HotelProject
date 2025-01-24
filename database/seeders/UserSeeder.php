<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => -1,
        ],
        );
        DB::table('users')->insert([
            'name' => 'user1',
            'email' => 'user1@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 0,
        ],
        );
        DB::table('users')->insert([
            'name' => 'employee',
            'email' => 'employee@gmail.com',
            'password' => Hash::make('123456789'),
            'role' => 1,
        ],
        );
    }
}
