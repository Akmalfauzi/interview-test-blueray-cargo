<?php

namespace Database\Seeders\V1\Permission;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard',
            'dashboard_data',
            'read_user',
            'create_user',
            'update_user',
            'delete_user',
            'read_role',
            'create_role',
            'update_role',
            'delete_role',
            'read_order',
            'create_order',
            'read_tracking',
            'read_tracking_history',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
