<?php

namespace App\Http\Traits;
use App\Models\User;
use App\Notifications\NewUser;

trait NotificationTrait
{
    public function RegisterNotifyAdmins(User $user)
    {
        try{
            $admins = User::role('admin')->get();
            foreach ($admins as $admin){
                $admin->notify(new NewUser($user));
            }
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

}

