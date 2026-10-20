@extends('adminlte::page')

@section('title', 'Division Types')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Division Types</h3></div>
        <div class="col-sm-6 text-end">
            @can('divisiontypes.create')
                <a href="{{ route('division-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Division Type
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
                           placeholder="Search division type">
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
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($divisionTypes as $divisionType)
                    <tr>
                        <td>{{ $divisionTypes->firstItem() + $loop->index }}</td>
                        <td>{{ $divisionType->divtypename }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($divisionType->description, 50) ?: '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $divisionType->active ? 'success' : 'secondary' }}">
                                {{ $divisionType->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('divisiontypes.edit')
                                <a href="{{ route('division-types.edit', $divisionType) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('divisiontypes.delete')
                                <form action="{{ route('division-types.destroy', $divisionType) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Delete this division type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No division types found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $divisionTypes->links() }}</div>
    </div>
@stop