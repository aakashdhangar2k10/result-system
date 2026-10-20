@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Users</h3></div>
        <div class="col-sm-6 text-end">
            @can('adminusers.create')
                <a href="{{ route('admin-users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash-error')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('admin-users.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Role</label>
                        <select name="roleid" class="form-select">
                            <option value="">All Roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(request('roleid') == $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Group</label>
                        <select name="groupid" class="form-select">
                            <option value="">All Groups</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->groupid }}" @selected(request('groupid') == $group->groupid)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Department</label>
                        <select name="deptid" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->deptid }}" @selected(request('deptid') == $department->deptid)>{{ $department->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('admin-users.index') }}" class="btn btn-light border">Reset</a>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span>Show</span>
                        <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span>Search:</span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Name or email">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="name" label="Name" />
                        <x-sortable-th column="email" label="Email" />
                        <th>Roles</th>
                        <th>Groups</th>
                        <th>Departments</th>
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    @php $everything = $user->all_departments || $user->roles->contains('name', 'admin'); @endphp
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->roles as $role)
                                <span class="badge text-bg-primary">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse ($user->groups as $group)
                                <span class="badge text-bg-info">{{ $group->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td class="small">
                            @if ($everything)
                                <span class="badge text-bg-success">All</span>
                            @else
                                {{ $user->departments->pluck('shortname')->implode(', ') ?: '—' }}
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $user->active ? 'success' : 'secondary' }}">
                                {{ $user->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            @can('adminusers.view')
                                <button type="button" class="btn btn-sm btn-success" title="View"
                                        data-bs-toggle="modal" data-bs-target="#userModal"
                                        data-title="{{ $user->name }}" data-url="{{ route('admin-users.show', $user) }}">
                                    <i class="bi bi-search"></i>
                                </button>
                            @endcan
                            @can('adminusers.edit')
                                <a href="{{ route('admin-users.edit', $user) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('adminusers.delete')
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin-users.destroy', $user) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the user {{ e($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries</span>
            {{ $users->links() }}
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('userModal');
            var body = document.getElementById('userModalBody');

            // keep the modal directly under <body> so the layout's containers can't clip it
            document.body.appendChild(modal);

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var url = button && button.dataset.url;
                if (!url) return;

                document.getElementById('userModalTitle').textContent = button.dataset.title || 'User';
                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-info"></div></div>';

                fetch(url, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error(response.status);
                        return response.text();
                    })
                    .then(function (html) { body.innerHTML = html; })
                    .catch(function () {
                        body.innerHTML = '<div class="alert alert-danger mb-0">Could not load the details. Please try again.</div>';
                    });
            });
        })();
    </script>
@stop