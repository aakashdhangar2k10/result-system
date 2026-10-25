@extends('adminlte::page')

@section('title', 'Student Import Log')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Student Import Log</h3></div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Students</a>
            @can('studentimports.create')
                <a href="{{ route('student-imports.create') }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-excel"></i> Add With Excel
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
        <form method="GET" action="{{ route('student-imports.index') }}">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select">
                            <option value="">All Courses</option>
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
                            <option value="">All Sessions</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->batchid }}" @selected(request('batchid') == $batch->batchid)>
                                    {{ $batch->course?->shortname }} {{ $batch->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">Search</button>
                        <a href="{{ route('student-imports.index') }}" class="btn btn-light border">Reset</a>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span>Show</span>
                    <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <th>Date</th>
                        <th>File</th>
                        <th>Course</th>
                        <th>Session</th>
                        <th>Uploaded By</th>
                        <th>Rows</th>
                        <th>Valid</th>
                        <th>Errors</th>
                        <th>Imported</th>
                        <th>Status</th>
                        <th style="width:130px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($imports as $import)
                    <tr>
                        <td>{{ $imports->firstItem() + $loop->index }}</td>
                        <td class="text-nowrap">{{ $import->created_at->format('d M Y, h:i A') }}</td>
                        <td class="text-break">{{ $import->file_name }}</td>
                        <td>{{ $import->course?->shortname ?? '—' }}</td>
                        <td>
                            {{ $import->batch?->label ?? '—' }}
                            @if ($import->batch?->branch)
                                <small class="text-muted">({{ $import->batch->branch->shortname }})</small>
                            @endif
                        </td>
                        <td>{{ $import->uploader?->name ?? '—' }}</td>
                        <td>{{ $import->total_rows }}</td>
                        <td>{{ $import->valid_rows }}</td>
                        <td>{{ $import->error_rows }}</td>
                        <td>
                            @if (in_array($import->status, ['imported', 'cleared']))
                                {{ $import->imported_rows }}
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge text-bg-{{ $import->status_color }}">{{ $import->status_label }}</span></td>
                        <td class="text-nowrap">
                            <a href="{{ route('student-imports.show', $import) }}" class="btn btn-sm btn-success" title="View">
                                <i class="bi bi-search"></i>
                            </a>
                            @if ($import->status === 'imported')
                                @can('studentimports.clear')
                                    <form action="{{ route('student-imports.clear', $import) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Clear the {{ $import->imported_rows }} students imported from {{ e($import->file_name) }}?')">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" title="Clear this sheet"><i class="bi bi-eraser"></i></button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No imports yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $imports->firstItem() ?? 0 }} to {{ $imports->lastItem() ?? 0 }} of {{ $imports->total() }} entries</span>
            {{ $imports->links() }}
        </div>
    </div>
@stop