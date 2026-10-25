@extends('adminlte::page')

@section('title', 'Groups')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Groups</h3></div>
        <div class="col-sm-6 text-end">
            @can('admingroups.create')
                <a href="{{ route('admin-groups.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Group</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash-error')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-primary card-outline">
        <form method="GET" action="{{ route('admin-groups.index') }}">
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
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Group name">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Access</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th style="width:130px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($groups as $group)
                    <tr>
                        <td>{{ $groups->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $group->name }}</strong></td>
                        <td>{{ $group->description ?: '—' }}</td>
                        <td>{{ $group->permissions_count }} of {{ $total }} permissions</td>
                        <td class="small">
                            <span class="badge text-bg-light border">{{ $group->users_count }}</span>
                            {{ $group->users->take(4)->pluck('name')->implode(', ') }}{{ $group->users_count > 4 ? ', ...' : '' }}
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $group->active ? 'success' : 'secondary' }}">{{ $group->active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-nowrap">
                            @can('admingroups.edit')
                                <a href="{{ route('admin-groups.edit', $group) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('admingroups.delete')
                                <form action="{{ route('admin-groups.destroy', $group) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete the group {{ e($group->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No groups found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $groups->firstItem() ?? 0 }} to {{ $groups->lastItem() ?? 0 }} of {{ $groups->total() }} entries</span>
            {{ $groups->links() }}
        </div>
    </div>
@stop