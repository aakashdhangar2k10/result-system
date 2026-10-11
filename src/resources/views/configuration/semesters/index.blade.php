@extends('adminlte::page')

@section('title', 'Semester List')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Semester List</h3></div>
        <div class="col-sm-6 text-end">
            @can('semesters.create')
                <a href="{{ route('semesters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Semester
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Search name or roman">
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
                        <th>Roman</th>
                        <th>Join Date</th>
                        <th>Status</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($semesters as $semester)
                    <tr>
                        <td>{{ $semesters->firstItem() + $loop->index }}</td>
                        <td>{{ $semester->name }}</td>
                        <td>{{ $semester->roman }}</td>
                        <td>{{ $semester->joindate?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $semester->active ? 'success' : 'secondary' }}">
                                {{ $semester->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('semesters.edit')
                                <a href="{{ route('semesters.edit', $semester) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('semesters.delete')
                                <form action="{{ route('semesters.destroy', $semester) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Delete this semester?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No semesters found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $semesters->links() }}</div>
    </div>
@stop