<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\UserRequest;
use App\Models\Administration\Group;
use App\Models\Department;
use App\Models\User;
use App\Services\AccessService;
use App\Support\DepartmentAccess;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(private AccessService $access) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:adminusers.view', only: ['index', 'show']),
            new Middleware('permission:adminusers.create', only: ['create', 'store']),
            new Middleware('permission:adminusers.edit', only: ['edit', 'update']),
            new Middleware('permission:adminusers.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = User::query()
            ->with(['roles', 'groups', 'departments'])
            ->when($request->roleid, fn($q, $id) => $q->whereHas('roles', fn($r) => $r->where('roles.id', $id)))
            ->when($request->groupid, fn($q, $id) => $q->whereHas('groups', fn($g) => $g->where('mark_group.groupid', $id)))
            ->when($request->deptid, fn($q, $id) => $q->whereHas('departments', fn($d) => $d->where('mark_department.deptid', $id)))
            ->when($request->status === 'active', fn($q) => $q->where('users.active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('users.active', false))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('users.name', 'like', "%{$search}%")->orWhere('users.email', 'like', "%{$search}%");
                });
            });

        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sortable = ['name' => 'users.name', 'email' => 'users.email', 'active' => 'users.active'];

        isset($sortable[$request->sort])
            ? $query->orderBy($sortable[$request->sort], $direction)
            : $query->orderBy('users.name');

        $query->orderBy('users.id');

        return view('administration.users.index', [
            'users' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
            'roles' => Role::where('guard_name', 'api')->orderBy('name')->get(),
            'groups' => Group::orderBy('name')->get(),
            'departments' => Department::orderBy('fullname')->get(),
        ]);
    }

    // popup with the roles, groups, departments and the final access of one user
    public function show(Request $request, User $user)
    {
        if (! $request->ajax()) {
            return redirect()->route('admin-users.index');
        }

        $user->load(['roles', 'groups', 'departments']);

        return view('administration.users._detail', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'access' => $this->access->summary($user->allPermissionNames()),
        ]);
    }

    public function create()
    {
        return view('administration.users.create', [
            'user' => new User(['active' => true, 'all_departments' => true]),
        ] + $this->formData());
    }

    public function store(UserRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $actor) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'active' => $request->boolean('active'),
                'all_departments' => $data['department_access'] === 'all',
            ]);
            if ($request->hasFile('photo')) {
                $user->update(['photo' => $request->file('photo')->store('profile-photos', 'public')]);
            }

            $this->syncAccess($user, $data, $actor);
        });

        return redirect()->route('admin-users.index')->with('success', 'User created.');
    }

    public function edit(Request $request, User $user)
    {
        $this->guardAdminUser($request->user(), $user);

        $user->load(['roles', 'groups', 'departments']);

        return view('administration.users.edit', ['user' => $user] + $this->formData());
    }

    public function update(UserRequest $request, User $user)
    {
        $actor = $request->user();
        $data = $request->validated();

        $this->guardAdminUser($actor, $user);

        // the roles the user will have after this save
        $adminRoleId = (int) Role::where('name', 'admin')->where('guard_name', 'api')->value('id');
        $roleIds = $this->access->keep(
            $user->roles->pluck('id')->map(fn($id) => (int) $id)->all(),
            array_map('intval', $data['roles'] ?? []),
            $this->access->grantableRoleIds($actor)
        );

        $isSelf = $user->id === $actor->id;
        $active = $request->boolean('active');

        if ($isSelf && ! $active) {
            throw ValidationException::withMessages(['active' => 'You cannot deactivate your own account.']);
        }

        if ($isSelf && $user->isAdmin() && ! in_array($adminRoleId, $roleIds, true)) {
            throw ValidationException::withMessages(['roles' => 'You cannot remove the administrator role from yourself.']);
        }

        $stillActiveAdmin = $active && in_array($adminRoleId, $roleIds, true);

        if ($user->active && $user->isAdmin() && ! $stillActiveAdmin && $this->otherActiveAdmins($user) === 0) {
            throw ValidationException::withMessages(['roles' => 'At least one active administrator is required.']);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'active' => $active,
            'all_departments' => $data['department_access'] === 'all',
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($request->boolean('remove_photo') && $user->photo) {
            Storage::disk('public')->delete($user->photo);
            $payload['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $payload['photo'] = $request->file('photo')->store('profile-photos', 'public');
        }

        DB::transaction(function () use ($user, $payload, $data, $actor) {
            $user->update($payload);
            $this->syncAccess($user, $data, $actor);
        });

        return redirect()->route('admin-users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();

        if ($user->id === $actor->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && ! $actor->isAdmin()) {
            return back()->with('error', 'Only an administrator can delete an administrator.');
        }

        if ($user->active && $user->isAdmin() && $this->otherActiveAdmins($user) === 0) {
            return back()->with('error', 'The last active administrator cannot be deleted.');
        }

        // soft delete: the user is hidden and cannot log in; the email becomes free for a new user
        $user->update([
            'is_delete' => true,
            'active' => false,
            'email' => mb_substr('deleted-' . $user->id . '-' . $user->email, 0, 255),
        ]);

        // sign the user out everywhere
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return redirect()->route('admin-users.index')->with('success', 'User deleted.');
    }

    // ------------------------------------------------------------------ helpers

    // roles, groups and departments: only the part the actor may give is changed
    private function syncAccess(User $user, array $data, User $actor): void
    {
        $user->loadMissing('roles');

        $roleIds = $this->access->keep(
            $user->roles->pluck('id')->map(fn($id) => (int) $id)->all(),
            array_map('intval', $data['roles'] ?? []),
            $this->access->grantableRoleIds($actor)
        );
        $user->syncRoles(Role::whereIn('id', $roleIds)->where('guard_name', 'api')->get()->all());

        $groupIds = $this->access->keep(
            DB::table('mark_group_user')->where('user_id', $user->id)->pluck('groupid')->map(fn($id) => (int) $id)->all(),
            array_map('intval', $data['groups'] ?? []),
            $this->access->grantableGroupIds($actor)
        );
        $user->groups()->sync($groupIds);

        if ($data['department_access'] === 'all') {
            $user->departments()->sync([]);
        } else {
            $departmentIds = $this->access->keep(
                DB::table('mark_user_department')->where('user_id', $user->id)->pluck('deptid')->map(fn($id) => (int) $id)->all(),
                array_map('intval', $data['departments'] ?? []),
                Department::pluck('deptid')->map(fn($id) => (int) $id)->all()   // the departments the actor can see
            );
            $user->departments()->sync($departmentIds);
        }

        DepartmentAccess::flush();
    }

    // only an administrator may open or change an administrator
    private function guardAdminUser(User $actor, User $user): void
    {
        abort_if($user->isAdmin() && ! $actor->isAdmin(), 403, 'Only an administrator can change an administrator.');
    }

    private function otherActiveAdmins(User $except): int
    {
        return User::role('admin', 'api')->where('users.id', '<>', $except->id)->where('users.active', true)->count();
    }

    private function formData(): array
    {
        $actor = auth()->user();

        return [
            'roles' => Role::where('guard_name', 'api')->orderBy('name')->get(),
            'groups' => Group::orderBy('name')->get(),
            'departments' => Department::orderBy('fullname')->get(),
            'grantableRoleIds' => $this->access->grantableRoleIds($actor),
            'grantableGroupIds' => $this->access->grantableGroupIds($actor),
            'canGiveAllDepartments' => DepartmentAccess::unrestricted(),
        ];
    }
}
