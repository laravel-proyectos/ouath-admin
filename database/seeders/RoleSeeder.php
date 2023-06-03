<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $create_user = Permission::create(['name' => 'create users']);
        $edit_user = Permission::create(['name' => 'edit users']);
        $delete_user = Permission::create(['name' => 'delete users']);

        $admin ->syncPermissions([$create_user, $edit_user, $delete_user]);
    }
}
