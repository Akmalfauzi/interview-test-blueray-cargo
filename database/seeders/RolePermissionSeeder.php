<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            
            // Profile
            'edit_profile',
            
            // Orders
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            
            // Tracking
            'view_tracking',
            'view_tracking_history',
            
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Roles
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $userRole = Role::create(['name' => 'user']);
        $userPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'edit_profile',
            'view_orders',
            'create_orders',
            'view_tracking',
            'view_tracking_history'
        ])->get();
        $userRole->syncPermissions($userPermissions);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());
    }
} 