<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(ProvinceTableSeeder::class);
//        $this->call(CountyTableSeeder::class);
//        $this->call(RolesTableSeeder::class);
        $this->call(ClientsTableSeeder::class);
        $this->call(WalletSeeder::class);
        // \App\Models\User::factory(10)->create();
    }
}
