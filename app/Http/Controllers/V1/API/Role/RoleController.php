<?php

namespace App\Http\Controllers\V1\API\Role;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\Role\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        // $roles = $this->roleService->getAllRoles();

        // return ApiResponse::success($roles);

        try {
            // Ambil parameter pagination dari request, dengan nilai default
            $perPage = $request->input('per_page', 10); // Default 10 item per halaman
            $page = $request->input('page', 1);         // Default halaman pertama

            // Validasi sederhana untuk per_page (opsional, tapi bagus untuk dimiliki)
            if (!is_numeric($perPage) || $perPage <= 0) {
                $perPage = 10;
            }
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }

            $search = $request->input('search', '');
            $query = Role::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            // Ambil data role dengan pagination
            // Asumsi Anda memiliki model Role yang terhubung ke tabel roles
            $roles = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles // Data pagination dari Laravel sudah memiliki struktur yang baik
                // seperti 'current_page', 'data' (item aktual), 'last_page', 'total', 'links', dll.
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving roles from API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles. ' . $e->getMessage(),
                'data' => null
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi request
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'description' => 'nullable|string|max:1000',
            // Tambahkan validasi untuk permissions jika ada
        ]);

        try {
            $role = Role::create($validated);
            // Jika Anda menggunakan Spatie Permissions, Anda bisa assign permissions di sini
            // if ($request->has('permissions')) {
            //    $role->syncPermissions($request->input('permissions'));
            // }

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], 201); // HTTP 201 Created

        } catch (\Exception $e) {
            Log::error('Error creating role via API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found',
                    'data' => null
                ], 404); // Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            Log::error("Error retrieving role ID {$id} from API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi request
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id . '|max:255', // Abaikan ID saat ini untuk unique check
            'description' => 'nullable|string|max:1000',
            // Tambahkan validasi untuk permissions jika ada
        ]);

        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            $role->update($validated);
            // Jika Anda menggunakan Spatie Permissions, Anda bisa sync permissions di sini
            // if ($request->has('permissions')) {
            //    $role->syncPermissions($request->input('permissions'));
            // }

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating role ID {$id} via API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Pertimbangkan validasi sebelum menghapus, misalnya jika role masih digunakan
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It is still assigned to users.'
                ], 400); // Bad Request
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]); // HTTP 200 OK atau 204 No Content

        } catch (\Exception $e) {
            Log::error("Error deleting role ID {$id} via API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role. ' . $e->getMessage()
            ], 500);
        }
    }
}