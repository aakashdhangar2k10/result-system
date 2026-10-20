<div class="row mb-3">
    <div class="col-md-4"><strong>Name:</strong> {{ $user->name }}</div>
    <div class="col-md-5"><strong>Email:</strong> {{ $user->email }}</div>
    <div class="col-md-3">
        <span class="badge text-bg-{{ $user->active ? 'success' : 'secondary' }}">{{ $user->active ? 'Active' : 'Inactive' }}</span>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <strong>Roles</strong><br>
        @forelse ($user->roles as $role)
            <span class="badge text-bg-primary">{{ $role->name }}</span>
        @empty
            <span class="text-muted">None</span>
        @endforelse
    </div>
    <div class="col-md-4">
        <strong>Groups</strong><br>
        @forelse ($user->groups as $group)
            <span class="badge text-bg-info">{{ $group->name }}</span>
        @empty
            <span class="text-muted">None</span>
        @endforelse
    </div>
    <div class="col-md-4">
        <strong>Departments</strong><br>
        @if ($isAdmin || $user->all_departments)
            <span class="badge text-bg-success">All departments</span>
        @else
            @forelse ($user->departments as $department)
                <span class="badge text-bg-secondary">{{ $department->shortname }}</span>
            @empty
                <span class="text-muted">None</span>
            @endforelse
        @endif
    </div>
</div>

<h6 class="fw-bold">Final access (roles and groups together)</h6>
@if ($access)
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead><tr><th style="width:40%">Module</th><th>Allowed</th></tr></thead>
            <tbody>
            @foreach ($access as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>
                        @foreach ($row['actions'] as $action)
                            <span class="badge text-bg-light border">{{ $action }}</span>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-muted">This user has no access yet. Give a role or a group.</div>
@endif