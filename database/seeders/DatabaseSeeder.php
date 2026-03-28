<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@spark.com',
            'password' => bcrypt('password'),
        ]);

        $account = User::factory()->create([
            'name' => 'accounting',
            'email' => 'account@spark.com',
            'password' => bcrypt('password'),
        ]);

        $waiter = User::factory()->create([
            'name' => 'waiter',
            'email' => 'waiter@spark.com',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('admin');
        $account->assignRole('accounting');
        $waiter->assignRole('waiter');

    }
}
