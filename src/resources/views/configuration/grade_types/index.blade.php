@extends('adminlte::page')

@section('title', 'Grade Types')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Grade Types</h3></div>
        <div class="col-sm-6 text-end">
            @can('gradetypes.create')
                <a href="{{ route('grade-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Grade Type
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

    <div class="card card-primary card-outline">
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Search grade type">
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
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($gradeTypes as $gradeType)
                    <tr>
                        <td>{{ $gradeTypes->firstItem() + $loop->index }}</td>
                        <td>{{ $gradeType->coursetypename }}</td>
                        <td>
                            <span class="badge text-bg-{{ $gradeType->active ? 'success' : 'secondary' }}">
                                {{ $gradeType->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $gradeType->created_at?->format('d M Y, h:i A') }}</td>
                        <td>{{ $gradeType->updated_at?->format('d M Y, h:i A') }}</td>
                        <td>
                            @can('gradetypes.edit')
                                <a href="{{ route('grade-types.edit', $gradeType) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-pencil-square"></i></a>
                            @endcan
                            @can('gradetypes.delete')
                                <form action="{{ route('grade-types.destroy', $gradeType) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Delete this grade type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No grade types found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $gradeTypes->links() }}</div>
    </div>
@stop