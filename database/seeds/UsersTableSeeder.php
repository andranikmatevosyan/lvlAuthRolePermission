<?php

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@mail.ru',
            'password' => bcrypt('123456')
        ]);

        $role = Role::orderBy('id', 'DESC')->first();

        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => 'App\User',
            'model_id' => $user->id
        ]);
    }
}
