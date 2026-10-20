@extends('adminlte::page')

@section('title', 'Student')

@section('content_header')
<div class="row">
    <div class="col-sm-6">
        <h3 class="mb-0">Student</h3>
    </div>
    <div class="col-sm-6 text-end">
        @can('studentimports.view')
        <a href="{{ route('student-imports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history"></i> Import Log
        </a>
        @endcan
        @can('studentimports.create')
        <a href="{{ route('student-imports.create') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-excel"></i> Add Students With Excel
        </a>
        @endcan
        @can('students.create')
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Student
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
    <form method="GET" action="{{ route('students.index') }}">
        {{-- keep the current sorting when filtering/searching --}}
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="dir" value="{{ request('dir') }}">

        <div class="card-header">
            <h3 class="card-title mb-0">Select criteria for Filter</h3>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Course</label>
                    <select name="courseid" class="form-select">
                        <option value="">Select Course</option>
                        @foreach ($courses as $course)
                        <option value="{{ $course->courseid }}" @selected(request('courseid')==$course->courseid)>
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
                        <option value="{{ $batch->batchid }}" @selected(request('batchid')==$batch->batchid)>
                            {{ $batch->course?->shortname }} {{ $batch->label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-info text-white">Search</button>
                    <a href="{{ route('students.index') }}" class="btn btn-light border">Reset</a>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span>Show</span>
                    <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($perPage==$size)>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span>Search:</span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto"
                        placeholder="Name, roll, enrollment...">
                </div>
            </div>
        </div>
    </form>

    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:70px">Sr. No</th>
                    <x-sortable-th column="rollno" label="Roll No." />
                    <x-sortable-th column="enrollmentno" label="Enrollment No." />
                    <x-sortable-th column="apaarid" label="APAAR ID" />
                    <x-sortable-th column="firstname" label="Student Name" />
                    <x-sortable-th column="fathername" label="Father's Name" />
                    <x-sortable-th column="mothername" label="Mother's Name" />
                    <x-sortable-th column="course" label="Course Name" />
                    <x-sortable-th column="session" label="Session" />
                    <x-sortable-th column="active" label="Status" />
                    <th style="width:110px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr>
                    <td>{{ $students->firstItem() + $loop->index }}</td>
                    <td>{{ $student->rollno }}</td>
                    <td>{{ $student->enrollmentno }}</td>
                    <td>{{ $student->apaarid ?: '—' }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->father_display }}</td>
                    <td>{{ $student->mother_display }}</td>
                    <td>{{ $student->course?->fullname ?? '—' }}</td>
                    <td>
                        {{ $student->batch?->label ?? '—' }}
                        @if ($student->batch?->branch)
                        <small class="text-muted">({{ $student->batch->branch->shortname }})</small>
                        @endif
                    </td>
                    <td>
                        @can('students.edit')
                        <form action="{{ route('students.status', $student) }}" method="POST" class="d-inline-flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" name="active" value="1"
                                class="btn btn-sm {{ $student->active ? 'btn-success' : 'btn-light border' }}">Active</button>
                            <button type="submit" name="active" value="0"
                                class="btn btn-sm {{ $student->active ? 'btn-light border' : 'btn-danger' }}">Deactive</button>
                        </form>
                        @else
                        <span class="badge text-bg-{{ $student->active ? 'success' : 'secondary' }}">
                            {{ $student->active ? 'Active' : 'Deactive' }}
                        </span>
                        @endcan
                    </td>
                    <td class="text-nowrap">
                        @can('students.view')
                        <button type="button" class="btn btn-sm btn-success" title="View"
                            data-bs-toggle="modal" data-bs-target="#studentModal"
                            data-url="{{ route('students.show', $student) }}">
                            <i class="bi bi-search"></i>
                        </button>
                        @endcan
                        @can('students.edit')
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-info text-white" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        @endcan
                        @can('students.delete')
                        <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Delete student {{ e($student->full_name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        <span>Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries</span>
        {{ $students->links() }}
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalTitle">Student Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('studentModal');
        var body = document.getElementById('studentModalBody');

        // keep the modal directly under <body> so the layout's containers can't clip it
        document.body.appendChild(modal);

        modal.addEventListener('show.bs.modal', function(event) {
            var url = event.relatedTarget && event.relatedTarget.dataset.url;
            if (!url) return;

            body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-info"></div></div>';

            fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.text();
                })
                .then(function(html) {
                    body.innerHTML = html;
                })
                .catch(function() {
                    body.innerHTML = '<div class="alert alert-danger mb-0">Could not load the details. Please try again.</div>';
                });
        });
    })();
</script>
@stop