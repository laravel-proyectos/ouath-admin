<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Merling Josue Ramirez Yugra',
            'email' => 'ramirezjry17@gmail.com',
            'password' => bcrypt('123456789'),
            'multi_session' => true,
        ]);

        $user->assignRole('admin');
        // User::factory(3) -> create();
    }
}
