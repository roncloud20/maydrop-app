<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = new User;
        $user->firstname = "oluwafemi";
        $user->middlename = "Pasting";
        $user->surname = "Gablin Pro Max";
        $user->email = "gablinpromaz@gmail.com";
        $user->password = Hash::make("Qwertyuiop@1");
        $user->phone = "08023023355";
        $user->email_verified_at = now();
        $user->user_role = "admin";
        $user->save();
    }
}
