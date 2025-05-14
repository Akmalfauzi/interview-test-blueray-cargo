<?php

namespace App\Http\Controllers\V1\API\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\V1\ApiResponse;
use App\Services\V1\API\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        try {
            // Get pagination parameters from request, with defaults
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Validate pagination parameters
            if (!is_numeric($perPage) || $perPage <= 0) {
                $perPage = 10;
            }
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }

            // Handle search
            $search = $request->input('search', '');
            $query = User::query()->with('roles');
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Get paginated users with their roles
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving users from API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users. ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);

            // Assign roles
            $user->roles()->sync($validated['roles']);

            // Load roles for response
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating user via API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with('roles')->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error("Error retrieving user ID {$id} from API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user. ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update user
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            
            $user->save();

            // Update roles
            $user->roles()->sync($validated['roles']);

            // Load roles for response
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating user ID {$id} via API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user. ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Prevent self-deletion
            if ($user->id === request()->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting user ID {$id} via API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRoles(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update roles
            $user->roles()->sync($validated['roles']);

            // Load roles for response
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'User roles updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating roles for user ID {$id} via API: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user roles. ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRoles($id)
    {
        $user = User::find($id);
        return response()->json([
            'success' => true,
            'message' => 'User roles retrieved successfully',
            'data' => $user->roles
        ]);
    }
}