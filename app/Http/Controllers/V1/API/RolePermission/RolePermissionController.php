<?php

namespace App\Http\Controllers\V1\API\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
    /**
     * Get all permissions for a specific role.
     */
    public function getPermissions(Role $role): JsonResponse
    {
        try {
            $permissions = Permission::all();
            $rolePermissions = $role->permissions->pluck('id')->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Permissions retrieved successfully',
                'data' => [
                    'permissions' => $permissions,
                    'role_permissions' => $rolePermissions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update permissions for a specific role.
     */
    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'required|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get permission names from IDs
            $permissionNames = Permission::whereIn('id', $request->permissions)
                ->pluck('name')
                ->toArray();

            // Sync permissions using names
            $role->syncPermissions($permissionNames);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully',
                'data' => [
                    'role' => $role->load('permissions')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 