@extends('adminlte::page')

@section('title', 'Session Subject')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Session Subject</h3></div>
        <div class="col-sm-6 text-end">
            @can('sessionsubjects.create')
                <a href="{{ route('session-subjects.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Session Subject
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
        <form method="GET" action="{{ route('session-subjects.index') }}">
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
                        <button class="btn btn-primary">Search</button>
                        <a href="{{ route('session-subjects.index') }}" class="btn btn-light border">Reset</a>
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
                               placeholder="Subject code or name">
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
                        <th>Subject Code</th>
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $row->course?->fullname ?? '—' }}</td>
                        <td>
                            {{ $row->batch?->label ?? '—' }}
                            @if ($row->batch?->branch)
                                <small class="text-muted">({{ $row->batch->branch->shortname }})</small>
                            @endif
                        </td>
                        <td>{{ $row->term_label }}</td>
                        <td>{{ $row->subject_codes ?: '—' }}</td>
                        <td>
                            @can('sessionsubjects.edit')
                                <a href="{{ route('session-subjects.edit', $row) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('sessionsubjects.delete')
                                <form action="{{ route('session-subjects.destroy', $row) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this session subject setup?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No session subjects found.</td></tr>
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