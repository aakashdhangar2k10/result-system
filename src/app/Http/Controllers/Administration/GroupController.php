<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\GroupRequest;
use App\Models\Administration\Group;
use App\Models\User;
use App\Services\AccessService;
use App\Support\DeleteGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class GroupController extends Controller implements HasMiddleware
{
    public function __construct(private AccessService $access)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:admingroups.view', only: ['index']),
            new Middleware('permission:admingroups.create', only: ['create', 'store']),
            new Middleware('permission:admingroups.edit', only: ['edit', 'update']),
            new Middleware('permission:admingroups.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $groups = Group::query()
            ->withCount(['permissions', 'users'])
            ->with('users')
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('administration.groups.index', [
            'groups' => $groups,
            'perPage' => $perPage,
            'total' => Permission::where('guard_name', 'api')->count(),
        ]);
    }

    public function create(Request $request)
    {
        return view('administration.groups.create', [
            'group' => new Group(['active' => true]),
            'checkedMembers' => [],
        ] + $this->formData($request->user(), []));
    }

    public function store(GroupRequest $request)
    {
        $actor = $request->user();

        DB::transaction(function () use ($request, $actor) {
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'active' => $request->boolean('active'),
            ]);

            $this->saveAccess($group, $request, $actor);
        });

        return redirect()->route('admin-groups.index')->with('success', 'Group created.');
    }

    public function edit(Request $request, Group $group)
    {
        $this->authorizeGroup($request->user(), $group);

        $group->load(['permissions', 'users']);

        return view('administration.groups.edit', [
            'group' => $group,
            'checkedMembers' => $group->users->pluck('id')->all(),
        ] + $this->formData($request->user(), $group->permissions->pluck('id')->all()));
    }

    public function update(GroupRequest $request, Group $group)
    {
        $actor = $request->user();
        $this->authorizeGroup($actor, $group);

        DB::transaction(function () use ($request, $group, $actor) {
            $group->update([
                'name' => $request->name,
                'description' => $request->description,
                'active' => $request->boolean('active'),
            ]);

            $this->saveAccess($group, $request, $actor);
        });

        return redirect()->route('admin-groups.index')->with('success', 'Group updated.');
    }

    public function destroy(Request $request, Group $group)
    {
        $this->authorizeGroup($request->user(), $group);

        if ($blocked = DeleteGuard::blocker($group)) {
            return back()->with('error', $blocked);
        }

        // soft delete, the same as the other modules
        $group->update(['is_delete' => true]);

        return redirect()->route('admin-groups.index')->with('success', 'Group deleted.');
    }

    // ------------------------------------------------------------------ helpers

    // you can only manage a group whose access you already have
    private function authorizeGroup(User $actor, Group $group): void
    {
        $group->loadMissing('permissions');

        abort_unless($this->access->canManageGroup($actor, $group), 403, 'You can only manage groups whose access you already have.');
    }

    private function saveAccess(Group $group, GroupRequest $request, User $actor): void
    {
        $grantable = $this->access->grantableNames($actor);

        $permissionIds = Permission::whereIn('id', $request->input('permissions', []))
            ->where('guard_name', 'api')
            ->pluck('name', 'id')
            ->filter(fn ($name) => in_array($name, $grantable, true))
            ->keys()
            ->all();

        $group->permissions()->sync($permissionIds);
        $group->users()->sync(array_map('intval', $request->input('members', [])));
    }

    private function formData(User $actor, array $checked): array
    {
        return [
            'matrix' => $this->access->matrix(),
            'checked' => $checked,
            'grantable' => $this->access->grantableNames($actor),
            'locked' => false,
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'active']),
        ];
    }
}