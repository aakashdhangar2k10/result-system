@extends('adminlte::page')

@section('title', 'Subject Types')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Subject Types</h3></div>
        <div class="col-sm-6 text-end">
            @can('subjecttypes.create')
                <a href="{{ route('subject-types.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Subject Type
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-primary card-outline">
        <form method="GET" action="{{ route('subject-types.index') }}">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

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
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto"
                               placeholder="Press Enter">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="subjecttypename" label="Subject Type" />
                        <x-sortable-th column="has_internal" label="Max Internal" />
                        <x-sortable-th column="has_external" label="Max External" />
                        <x-sortable-th column="has_grade" label="Add Grade" />
                        <x-sortable-th column="is_optional" label="Optional" />
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($subjectTypes as $subjectType)
                    <tr>
                        <td>{{ $subjectTypes->firstItem() + $loop->index }}</td>
                        <td>{{ $subjectType->subjecttypename }}</td>
                        <td>{!! $subjectType->has_internal ? '<span class="badge text-bg-primary">Asked</span>' : '<span class="text-muted">—</span>' !!}</td>
                        <td>{!! $subjectType->has_external ? '<span class="badge text-bg-primary">Asked</span>' : '<span class="text-muted">—</span>' !!}</td>
                        <td>{!! $subjectType->has_grade ? '<span class="badge text-bg-primary">Asked</span>' : '<span class="text-muted">—</span>' !!}</td>
                        <td>{!! $subjectType->is_optional ? '<span class="badge text-bg-info">Optional</span>' : '<span class="text-muted">—</span>' !!}</td>
                        <td>
                            <span class="badge text-bg-{{ $subjectType->active ? 'success' : 'secondary' }}">
                                {{ $subjectType->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('subjecttypes.edit')
                                <a href="{{ route('subject-types.edit', $subjectType) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('subjecttypes.delete')
                                <form action="{{ route('subject-types.destroy', $subjectType) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this subject type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No subject types found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $subjectTypes->firstItem() ?? 0 }} to {{ $subjectTypes->lastItem() ?? 0 }} of {{ $subjectTypes->total() }} entries</span>
            {{ $subjectTypes->links() }}
        </div>
    </div>
@stop