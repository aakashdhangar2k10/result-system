@extends('adminlte::page')

@section('title', 'Optional Subject Student')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Optional Subject Student</h3></div>
        <div class="col-sm-6 text-end">
            @can('optionalsubjectstudents.create')
                <a href="{{ route('optional-subject-students.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Optional Subject Students
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
        <form method="GET" action="{{ route('optional-subject-students.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-header">
                <h3 class="card-title mb-0">Select criteria for Filter</h3>
            </div>

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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semid" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->semid }}" @selected(request('semid') == $term->semid)>
                                    {{ \App\Support\Term::base($term->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('optional-subject-students.index') }}" class="btn btn-light border">Reset</a>
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
                               placeholder="Subject or roll no.">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="course" label="Course" />
                        <x-sortable-th column="session" label="Session" />
                        <x-sortable-th column="term" label="Semester" />
                        <x-sortable-th column="subject" label="Subject" />
                        <th>Roll No.</th>
                        <th style="width:130px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $row->subject?->course?->fullname ?? '—' }}</td>
                        <td>
                            {{ $row->batch?->label ?? '—' }}
                            @if ($row->batch?->branch)
                                <small class="text-muted">({{ $row->batch->branch->shortname }})</small>
                            @endif
                        </td>
                        <td>{{ $row->subject?->term_label }}</td>
                        <td>{{ $row->subject?->subjectname }} ({{ $row->subject?->subjectcode }})</td>
                        <td class="small">
                            {{ $row->students->pluck('rollno')->implode(', ') ?: '—' }}
                            <span class="badge text-bg-light border">{{ $row->students->count() }} students</span>
                        </td>
                        <td class="text-nowrap">
                            @if ($row->list_locked)
                                <span class="badge text-bg-secondary" title="The result of this semester is generated">
                                    <i class="bi bi-lock-fill"></i> Locked
                                </span>
                            @else
                                @can('optionalsubjectstudents.edit')
                                    <a href="{{ route('optional-subject-students.edit', $row) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                                @can('optionalsubjectstudents.delete')
                                    <form action="{{ route('optional-subject-students.destroy', $row) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the optional subject students of {{ e($row->subject?->subjectname) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No optional subject students found.</td></tr>
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