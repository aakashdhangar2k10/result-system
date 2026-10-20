<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\RoleRequest;
use App\Models\User;
use App\Services\AccessService;
use App\Support\DeleteGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(private AccessService $access)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:adminroles.view', only: ['index']),
            new Middleware('permission:adminroles.create', only: ['create', 'store']),
            new Middleware('permission:adminroles.edit', only: ['edit', 'update']),
            new Middleware('permission:adminroles.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $roles = Role::query()
            ->where('guard_name', 'api')
            ->withCount(['permissions', 'users'])
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('administration.roles.index', [
            'roles' => $roles,
            'perPage' => $perPage,
            'total' => Permission::where('guard_name', 'api')->count(),
        ]);
    }

    public function create(Request $request)
    {
        return view('administration.roles.create', [
            'role' => new Role(),
            'matrix' => $this->access->matrix(),
            'checked' => [],
            'grantable' => $this->access->grantableNames($request->user()),
            'locked' => false,
        ]);
    }

    public function store(RoleRequest $request)
    {
        $actor = $request->user();

        DB::transaction(function () use ($request, $actor) {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);
            $role->syncPermissions($this->chosenPermissions($request, $actor));
        });

        return redirect()->route('admin-roles.index')->with('success', 'Role created.');
    }

    public function edit(Request $request, Role $role)
    {
        $this->authorizeRole($request->user(), $role);

        return view('administration.roles.edit', [
            'role' => $role,
            'matrix' => $this->access->matrix(),
            'checked' => $role->permissions->pluck('id')->all(),
            'grantable' => $this->access->grantableNames($request->user()),
            'locked' => $role->name === 'admin',
        ]);
    }

    public function update(RoleRequest $request, Role $role)
    {
        $actor = $request->user();
        $this->authorizeRole($actor, $role);

        DB::transaction(function () use ($request, $role, $actor) {
            if ($role->name === 'admin') {
                // the administrator always has everything, including permissions added later
                $role->syncPermissions(Permission::where('guard_name', 'api')->get());
                return;
            }

            // the system roles keep their names
            if (! in_array($role->name, config('access.system_roles'), true)) {
                $role->update(['name' => $request->name]);
            }

            $role->syncPermissions($this->chosenPermissions($request, $actor));
        });

        return redirect()->route('admin-roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role)
    {
        $this->authorizeRole($request->user(), $role);

        if (in_array($role->name, config('access.system_roles'), true)) {
            return back()->with('error', "The system role '{$role->name}' cannot be deleted.");
        }

        if ($blocked = DeleteGuard::blocker($role)) {
            return back()->with('error', $blocked);
        }

        $role->delete();

        return redirect()->route('admin-roles.index')->with('success', 'Role deleted.');
    }

    // ------------------------------------------------------------------ helpers

    // you can only manage a role whose access you already have
    private function authorizeRole(User $actor, Role $role): void
    {
        abort_unless($this->access->canManageRole($actor, $role), 403, 'You can only manage roles whose access you already have.');
    }

    // the ticked permissions, limited to what the actor may give
    private function chosenPermissions(RoleRequest $request, User $actor): array
    {
        $grantable = $this->access->grantableNames($actor);

        return Permission::whereIn('id', $request->input('permissions', []))
            ->where('guard_name', 'api')
            ->pluck('name')
            ->filter(fn ($name) => in_array($name, $grantable, true))
            ->values()
            ->all();
    }
}