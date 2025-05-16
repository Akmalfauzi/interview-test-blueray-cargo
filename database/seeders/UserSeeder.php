<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        $admin->givePermissionTo(Permission::all());

        // Create regular user
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        $user->givePermissionTo(Permission::whereIn('name', [
            'view_dashboard',
            'edit_profile',
            'view_orders',
            'create_orders',
            'view_tracking',
            'view_tracking_history'
        ])->get());
        
    }
} 