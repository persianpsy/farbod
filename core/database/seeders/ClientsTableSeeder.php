<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        \Laravel\Passport\Client::create([
//            'name' => 'application',
//            'secret' => '6mMFRQvXBVRjqx2Zpe6PH2NIpjmJaIZAhUBV2Ef4',
//            'provider' => 'users',
//            'redirect' => 'http://localhost/',
//            'personal_access_client' => 0,
//            'revoked' => 0,
//            'password_client' => 1
//        ]);
        $user1 = User::create([
            'first_name'=>'مدیریت',
            'last_name'=>'2',
            'cellphone'=>'0912',
            'password' => Hash::make('123'),
            'location' => 'IR'
        ]);
//        $user1->syncRoles('admin');
    }
}
