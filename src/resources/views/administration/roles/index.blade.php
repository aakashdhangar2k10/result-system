@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Roles</h3></div>
        <div class="col-sm-6 text-end">
            @can('adminroles.create')
                <a href="{{ route('admin-roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
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
        <form method="GET" action="{{ route('admin-roles.index') }}">
            <div class="card-body">
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
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Role name">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <th>Role</th>
                        <th>Type</th>
                        <th>Access</th>
                        <th>Users</th>
                        <th style="width:130px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($roles as $role)
                    @php $isSystem = in_array($role->name, config('access.system_roles'), true); @endphp
                    <tr>
                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td>
                            <span class="badge text-bg-{{ $isSystem ? 'secondary' : 'info' }}">{{ $isSystem ? 'System' : 'Custom' }}</span>
                        </td>
                        <td>
                            {{ $role->name === 'admin' ? 'Everything' : $role->permissions_count . ' of ' . $total . ' permissions' }}
                        </td>
                        <td>{{ $role->users_count }}</td>
                        <td class="text-nowrap">
                            @can('adminroles.edit')
                                <a href="{{ route('admin-roles.edit', $role) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('adminroles.delete')
                                @unless ($isSystem)
                                    <form action="{{ route('admin-roles.destroy', $role) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the role {{ e($role->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No roles found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries</span>
            {{ $roles->links() }}
        </div>
    </div>
@stop