<?php

namespace Tests\Unit\Services\V1\API\Role;

use App\Services\V1\API\Role\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleService = new RoleService();
    }

    public function test_get_all_roles()
    {
        // Create test roles
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'user']);
        $role3 = Role::create(['name' => 'manager']);

        // Get all roles
        $roles = $this->roleService->getAllRoles();

        // Assert roles were returned
        $this->assertCount(3, $roles);
        $this->assertEquals('admin', $roles[0]->name);
        $this->assertEquals('user', $roles[1]->name);
        $this->assertEquals('manager', $roles[2]->name);
    }

    public function test_create_role()
    {
        // Test role data
        $roleData = [
            'name' => 'editor',
            'guard_name' => 'web'
        ];

        // Create role
        $role = $this->roleService->createRole($roleData);

        // Assert role was created
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('editor', $role->name);
        $this->assertEquals('web', $role->guard_name);

        // Assert role exists in database
        $this->assertDatabaseHas('roles', [
            'name' => 'editor',
            'guard_name' => 'web'
        ]);
    }

    public function test_update_role()
    {
        // Create test role
        $role = Role::create(['name' => 'editor']);

        // Test role data
        $roleData = [
            'name' => 'senior_editor',
            'guard_name' => 'web'
        ];

        // Update role
        $updatedRole = $this->roleService->updateRole($role, $roleData);

        // Assert role was updated
        $this->assertInstanceOf(Role::class, $updatedRole);
        $this->assertEquals('senior_editor', $updatedRole->name);
        $this->assertEquals('web', $updatedRole->guard_name);

        // Assert role was updated in database
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'senior_editor',
            'guard_name' => 'web'
        ]);
    }

    public function test_delete_role()
    {
        // Create test role
        $role = Role::create(['name' => 'editor']);

        // Delete role
        $deletedRole = $this->roleService->deleteRole($role);

        // Assert role was deleted
        $this->assertInstanceOf(Role::class, $deletedRole);
        $this->assertEquals('editor', $deletedRole->name);

        // Assert role was deleted from database
        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
            'name' => 'editor'
        ]);
    }

    public function test_get_role_by_id()
    {
        // Create test role
        $role = Role::create(['name' => 'editor']);

        // Get role by ID
        $foundRole = $this->roleService->getRoleById($role->id);

        // Assert role was found
        $this->assertInstanceOf(Role::class, $foundRole);
        $this->assertEquals($role->id, $foundRole->id);
        $this->assertEquals('editor', $foundRole->name);
    }

    public function test_get_role_by_field()
    {
        // Create test role
        $role = Role::create(['name' => 'editor']);

        // Get role by field
        $foundRole = $this->roleService->getRoleByField('name', 'editor');

        // Assert role was found
        $this->assertInstanceOf(Role::class, $foundRole);
        $this->assertEquals($role->id, $foundRole->id);
        $this->assertEquals('editor', $foundRole->name);
    }
} 