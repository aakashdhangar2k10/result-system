@extends('adminlte::page')

@section('title', 'Student List')

@section('content_header')
<div class="row">
    <div class="col-sm-6">
        <h3 class="mb-0">Student List</h3>
    </div>
    <div class="col-sm-6 text-end">
        @can('studentlists.create')
        <a href="{{ route('student-lists.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add
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
@if ($errors->has('note'))
<div class="alert alert-danger">{{ $errors->first('note') }}</div>
@endif

<div class="card">
    <form method="GET" action="{{ route('student-lists.index') }}">
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
                        <option value="{{ $course->courseid }}" @selected(request('courseid')==$course->courseid)>
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
                        <option value="{{ $batch->batchid }}" @selected(request('batchid')==$batch->batchid)>
                            {{ $batch->course?->shortname }} {{ $batch->label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Lock</label>
                    <select name="lock" class="form-select">
                        <option value="">All</option>
                        <option value="unlocked" @selected(request('lock')==='unlocked' )>Unlocked</option>
                        <option value="locked" @selected(request('lock')==='locked' )>Locked</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-info text-white">Search</button>
                    <a href="{{ route('student-lists.index') }}" class="btn btn-light border">Reset</a>
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
                        placeholder="Course name">
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
                    <x-sortable-th column="month" label="Semester Month" />
                    <x-sortable-th column="total" label="Total Student" />
                    <x-sortable-th column="added" label="Added Student" />
                    <th style="width:150px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lists as $list)
                @php
                $semester = $list->semester;
                $pending = max(0, $list->total_students - ($list->added_students - $list->extra_students));
                @endphp
                <tr>
                    <td>{{ $lists->firstItem() + $loop->index }}</td>
                    <td>{{ $semester->course?->fullname ?? '—' }}</td>
                    <td>
                        {{ $semester->batch?->label ?? '—' }}
                        @if ($semester->batch?->branch)
                        <small class="text-muted">({{ $semester->batch->branch->shortname }})</small>
                        @endif
                    </td>
                    <td>{{ \App\Support\Term::base($semester->term?->name) }}</td>
                    <td>{{ $semester->semestermonth?->format('F-Y') }}</td>
                    <td>{{ $list->total_students }}</td>
                    <td>
                        {{ $list->added_students }}
                        @if ($list->extra_students > 0)
                        <small class="text-muted">(+{{ $list->extra_students }} back/repeat)</small>
                        @endif
                        @if ($pending > 0)
                        <span class="badge text-bg-danger" title="Students of the session that are not on this list">
                            {{ $pending }} pending
                        </span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        @can('studentlists.view')
                        <button type="button" class="btn btn-sm btn-success" title="View"
                            data-bs-toggle="modal" data-bs-target="#studentListModal"
                            data-url="{{ route('student-lists.show', $list) }}">
                            <i class="bi bi-search"></i>
                        </button>
                        @endcan

                        @if ($list->is_locked)
                        @can('studentlists.unlock')
                        <button type="button" class="btn btn-sm btn-secondary" title="Locked: click to unlock"
                            data-bs-toggle="modal" data-bs-target="#lockModal"
                            data-mode="unlock"
                            data-url="{{ route('student-lists.unlock', $list) }}"
                            data-label="{{ $semester->course?->shortname }} · {{ $semester->batch?->label }} · {{ \App\Support\Term::base($semester->term?->name) }}">
                            <i class="bi bi-lock-fill"></i> Locked
                        </button>
                        @else
                        <span class="badge text-bg-secondary" title="The list is locked">
                            <i class="bi bi-lock-fill"></i> Locked
                        </span>
                        @endcan
                        @else
                        @can('studentlists.edit')
                        <a href="{{ route('student-lists.edit', $list) }}" class="btn btn-sm btn-info text-white" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        @endcan
                        @can('studentlists.lock')
                        <button type="button" class="btn btn-sm btn-warning" title="Lock this list"
                            data-bs-toggle="modal" data-bs-target="#lockModal"
                            data-mode="lock"
                            data-url="{{ route('student-lists.lock', $list) }}"
                            data-label="{{ $semester->course?->shortname }} · {{ $semester->batch?->label }} · {{ \App\Support\Term::base($semester->term?->name) }}">
                            <i class="bi bi-unlock"></i>
                        </button>
                        @endcan
                        @can('studentlists.delete')
                        <form action="{{ route('student-lists.destroy', $list) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Delete this student list?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No student lists found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    

    <div class="card-footer d-flex justify-content-between align-items-center">
        <span>Showing {{ $lists->firstItem() ?? 0 }} to {{ $lists->lastItem() ?? 0 }} of {{ $lists->total() }} entries</span>
        {{ $lists->links() }}
    </div>
</div>

<div class="modal fade" id="studentListModal" tabindex="-1" aria-labelledby="studentListModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentListModalTitle">Student List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentListModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="lockForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="lockModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 fw-bold" id="lockModalList"></p>
                <p class="text-muted" id="lockModalText"></p>

                <label class="form-label fw-bold" for="lockNote" id="lockNoteLabel"></label>
                <textarea name="note" id="lockNote" rows="3" maxlength="255" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn" id="lockSubmit"></button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('lockModal');
        var form = document.getElementById('lockForm');
        var note = document.getElementById('lockNote');
        var submit = document.getElementById('lockSubmit');

        // keep the modal directly under <body> so the layout's containers can't clip it
        document.body.appendChild(modal);

        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (!button) return;

            var unlock = button.dataset.mode === 'unlock';

            form.action = button.dataset.url;
            note.value = '';
            note.required = unlock;
            note.minLength = unlock ? 5 : 0;
            note.placeholder = unlock ? 'Why does this list need to be changed?' : 'Optional';

            document.getElementById('lockModalTitle').textContent = unlock ? 'Unlock student list' : 'Lock student list';
            document.getElementById('lockModalList').textContent = button.dataset.label;
            document.getElementById('lockModalText').textContent = unlock ?
                'Students can be added to or removed from this list again. Write the reason, it is kept in the lock history.' :
                'Nobody can edit or delete this list until it is unlocked. Lock it when the list is final.';
            document.getElementById('lockNoteLabel').textContent = unlock ? 'Reason for unlocking' : 'Note (optional)';

            submit.className = 'btn ' + (unlock ? 'btn-danger' : 'btn-warning');
            submit.textContent = unlock ? 'Unlock' : 'Lock';
        });

        // a double click must not send the form twice
        form.addEventListener('submit', function() {
            submit.disabled = true;
        });
    })();
</script>
<script>
    (function() {
        var modal = document.getElementById('studentListModal');
        var body = document.getElementById('studentListModalBody');

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