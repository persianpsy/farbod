<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'api'
            ],
            [
                'name' => 'user',
                'guard_name' => 'api'
            ],
            [
                'name' => 'doctor',
                'guard_name' => 'api'
            ],
        ];
        Role::insert($roles);
    }
}
