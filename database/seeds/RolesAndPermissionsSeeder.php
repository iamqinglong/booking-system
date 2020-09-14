<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'add']);
        Permission::create(['name' => 'edit']);
        Permission::create(['name' => 'delete']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'User', 'guard_name' => 'web']);
        $role->givePermissionTo('edit');

        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all());

        $user = User::create([
            'name' => 'Admin Booker',
            'email' => 'admin@booking.com',
            'password' => bcrypt('secret123'),
        ]);

        $user->assignRole('Admin');

        $user = User::create([
            'name' => 'Tester 1',
            'email' => 'tester1@booking.com',
            'password' => bcrypt('secret123'),
        ]);

        $user->assignRole('User');

    }
}
