@extends('adminlte::page')

@section('title', 'Semester Exam')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Semester Exam</h3></div>
        <div class="col-sm-6 text-end">
            @can('semesterexams.create')
                <a href="{{ route('semester-exams.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Semester Exam
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
        <form method="GET" action="{{ route('semester-exams.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
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
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('semester-exams.index') }}" class="btn btn-light border">Reset</a>
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
                        <x-sortable-th column="attempt" label="Attempt" />
                        <x-sortable-th column="exammonth" label="Exam Month" />
                        <x-sortable-th column="resultdate" label="Result Date" />
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
                        <td>{{ $row->attempt }}</td>
                        <td>{{ $row->exammonth?->format('F-Y') }}</td>
                        <td>{{ $row->resultdate?->format('d F, Y') ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($row->description, 40) ?: '—' }}</td>
                        <td>
                            @can('semesterexams.edit')
                                <a href="{{ route('semester-exams.edit', $row) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('semesterexams.delete')
                                <form action="{{ route('semester-exams.destroy', $row) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this semester exam?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No semester exams found.</td></tr>
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