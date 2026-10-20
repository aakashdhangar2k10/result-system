@extends('adminlte::page')

@section('title', 'Courses')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Courses</h3></div>
        <div class="col-sm-6 text-end">
            @can('courses.create')
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Course
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
        <form method="GET" action="{{ route('courses.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Department</label>
                        <select name="deptid" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->deptid }}"
                                    @selected(request('deptid') == $department->deptid)>
                                    {{ $department->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Course Type</label>
                        <select name="coursetypeid" class="form-select">
                            <option value="">All Course Types</option>
                            @foreach ($courseTypes as $courseType)
                                <option value="{{ $courseType->coursetypeid }}"
                                    @selected(request('coursetypeid') == $courseType->coursetypeid)>
                                    {{ $courseType->coursetypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Academic Structure</label>
                        <select name="structure_type" class="form-select">
                            <option value="">All</option>
                            @foreach ($structures as $value => $label)
                                <option value="{{ $value }}" @selected(request('structure_type') == $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('courses.index') }}" class="btn btn-light border">Reset</a>
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
                        <x-sortable-th column="department" label="Department" />
                        <x-sortable-th column="coursetype" label="Course Type" />
                        <x-sortable-th column="shortname" label="Course Name" />
                        <x-sortable-th column="fullname" label="Course Full Name" />
                        <x-sortable-th column="structure_type" label="Structure" />
                        <x-sortable-th column="total_terms" label="Duration" />
                        <x-sortable-th column="has_branch" label="Branches" />
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td>{{ $courses->firstItem() + $loop->index }}</td>
                        <td>{{ $course->department?->fullname ?? '—' }}</td>
                        <td>{{ $course->courseType?->coursetypename ?? '—' }}</td>
                        <td>{{ $course->shortname }}</td>
                        <td>{{ $course->fullname }}</td>
                        <td>{{ $structures[$course->structure_type] ?? $course->structure_type }}</td>
                        <td>{{ $course->duration_label }}</td>
                        <td>
                            <span class="badge text-bg-{{ $course->has_branch ? 'primary' : 'light border text-dark' }}">
                                {{ $course->has_branch ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $course->active ? 'success' : 'secondary' }}">
                                {{ $course->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('courses.edit')
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('courses.delete')
                                <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this course?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No courses found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $courses->firstItem() ?? 0 }} to {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} entries</span>
            {{ $courses->links() }}
        </div>
    </div>
@stop