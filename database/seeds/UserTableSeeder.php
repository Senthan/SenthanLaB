
<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{

    public function run()
    {
        $role_regular_user = Role::where('name', 'user')->first();
        $role_admin_user = Role::where('name', 'admin')->first();

        $regular_user = new User();
        $regular_user->name = 'Senthan Shanmugaratnam';
        $regular_user->email = 'senthaneng@gmail.com';
        $regular_user->password = Hash::make('senthan');
        $regular_user->save();
        $regular_user->roles()->attach($role_admin_user);

        $admin_user = new User();
        $admin_user->name = 'Senthan';
        $admin_user->email = 'senthanboss@gmail.com';
        $admin_user->password = Hash::make('senthan');
        $admin_user->save();
        $admin_user->roles()->attach($role_regular_user);
    }
}
