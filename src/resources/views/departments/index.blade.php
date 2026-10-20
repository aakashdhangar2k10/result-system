@extends('adminlte::page')

@section('title', 'Departments')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Departments</h3></div>
        <div class="col-sm-6 text-end">
            @can('departments.create')
                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Department
                </a>
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
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Search full name or short name">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary">Search</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Logo</th>
                        <th>Full Name</th>
                        <th>Short Name</th>
                        <th>Affiliation</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td>{{ $departments->firstItem() + $loop->index }}</td>
                        <td>
                            @if ($department->logo)
                                <img src="{{ $department->logo_url }}" alt="" style="height:36px">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $department->fullname }}</td>
                        <td>{{ $department->shortname }}</td>
                        <td>
                            {{ $department->affilationshortname ?? '—' }}
                            @if ($department->affilationyear)
                                <small class="text-muted">({{ $department->affilationyear }})</small>
                            @endif
                        </td>
                        <td>{{ $department->joindate?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $department->active ? 'success' : 'secondary' }}">
                                {{ $department->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('departments.edit')
                                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('departments.delete')
                                <form action="{{ route('departments.destroy', $department) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No departments found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $departments->links() }}</div>
    </div>
@stop