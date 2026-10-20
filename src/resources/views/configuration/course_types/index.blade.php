@extends('adminlte::page')

@section('title', 'Course Types')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Course Types</h3></div>
        <div class="col-sm-6 text-end">
            @can('coursetypes.create')
                <a href="{{ route('course-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Course Type
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
                        <th style="width:60px">Sr. No.</th>
                        <th>Full Name</th>
                        <th>Short Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($courseTypes as $courseType)
                    <tr>
                        <td>{{ $courseTypes->firstItem() + $loop->index }}</td>
                        <td>{{ $courseType->fullname }}</td>
                        <td>{{ $courseType->shortname }}</td>
                        <td>{{ $courseType->description }}</td>
                        <td>
                            <span class="badge text-bg-{{ $courseType->active ? 'success' : 'secondary' }}">
                                {{ $courseType->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('coursetypes.edit')
                                <a href="{{ route('course-types.edit', $courseType) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('coursetypes.delete')
                                <form action="{{ route('course-types.destroy', $courseType) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Delete this course type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No course types found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $courseTypes->links() }}</div>
    </div>
@stop