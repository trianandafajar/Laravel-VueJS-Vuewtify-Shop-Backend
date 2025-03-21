<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(
            [
                'name' => 'Rina',
                'email' => 'rina@example.com',
                'password' => bcrypt('password123'),
                'roles' => json_encode(['ADMIN']),
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Tommy',
                'email' => 'tommy@example.com',
                'password' => bcrypt('password123'),
                'roles' => json_encode(['CUSTOMER']),
                'status' => 'INACTIVE',
            ]
        );
    }
}
