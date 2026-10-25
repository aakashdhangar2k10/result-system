@extends('adminlte::page')

@section('title', 'Semester')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Semester</h3></div>
        <div class="col-sm-6 text-end">
            @can('coursesemesters.create')
                <a href="{{ route('course-semesters.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Semester
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
        <form method="GET" action="{{ route('course-semesters.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select">
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(request('courseid') == $course->courseid)>
                                    {{ $course->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Session</label>
                        <select name="batchid" class="form-select">
                            <option value="">Select Session</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->batchid }}" @selected(request('batchid') == $batch->batchid)>
                                    {{ $batch->course?->shortname }} {{ $batch->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">Search</button>
                        <a href="{{ route('course-semesters.index') }}" class="btn btn-light border">Reset</a>
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
                               placeholder="Description, press Enter">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="course" label="Course Name" />
                        <x-sortable-th column="session" label="Session" />
                        <x-sortable-th column="term" label="Semester / Year" />
                        <x-sortable-th column="semestermonth" label="Semester Month" />
                        <x-sortable-th column="validcredit" label="Valid Credit" />
                        <th>Description</th>
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $row->course?->fullname ?? '—' }}</td>
                        <td>{{ $row->batch?->label ?? '—' }}</td>
                        <td>{{ $row->term_label }}</td>
                        <td>{{ $row->semestermonth?->format('F-Y') }}</td>
                        <td>{{ $row->validcredit }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($row->description, 40) ?: '—' }}</td>
                        <td>
                            @can('coursesemesters.edit')
                                <a href="{{ route('course-semesters.edit', $row) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('coursesemesters.delete')
                                <form action="{{ route('course-semesters.destroy', $row) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this semester?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No semesters found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries</span>
            {{ $rows->links() }}
        </div>
    </div>
@stop