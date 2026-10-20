@extends('adminlte::page')

@section('title', 'Session Grade')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Session Grade</h3></div>
        <div class="col-sm-6 text-end">
            @can('sessiongrades.create')
                <a href="{{ route('session-grades.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Session Grade
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
        <form method="GET" action="{{ route('session-grades.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select">
                            <option value="">All Courses</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(request('courseid') == $course->courseid)>
                                    {{ $course->shortname }} – {{ $course->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Grade Type</label>
                        <select name="gradetypeid" class="form-select">
                            <option value="">All Grade Types</option>
                            @foreach ($gradeTypes as $gradeType)
                                <option value="{{ $gradeType->coursetypeid }}" @selected(request('gradetypeid') == $gradeType->coursetypeid)>
                                    {{ $gradeType->coursetypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Division Type</label>
                        <select name="divtypeid" class="form-select">
                            <option value="">All Division Types</option>
                            @foreach ($divisionTypes as $divisionType)
                                <option value="{{ $divisionType->divtypeid }}" @selected(request('divtypeid') == $divisionType->divtypeid)>
                                    {{ $divisionType->divtypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('session-grades.index') }}" class="btn btn-light border">Reset</a>
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
                        <x-sortable-th column="course" label="Course" />
                        <x-sortable-th column="session" label="Session" />
                        <x-sortable-th column="gradetype" label="Grade Type" />
                        <x-sortable-th column="divisiontype" label="Division Type" />
                        <th>Description</th>
                        <x-sortable-th column="active" label="Status" />
                       <th style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($sessionGrades as $sessionGrade)
                    <tr>
                        <td>{{ $sessionGrades->firstItem() + $loop->index }}</td>
                        <td>{{ $sessionGrade->course?->shortname ?? '—' }}</td>
                        <td>
                            {{ $sessionGrade->batch?->label ?? '—' }}
                            @if ($sessionGrade->batch?->branch)
                                <small class="text-muted">({{ $sessionGrade->batch->branch->shortname }})</small>
                            @endif
                        </td>
                        <td>{{ $sessionGrade->gradeType?->coursetypename ?? '—' }}</td>
                        <td>{{ $sessionGrade->divisionType?->divtypename ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($sessionGrade->description, 40) ?: '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $sessionGrade->active ? 'success' : 'secondary' }}">
                                {{ $sessionGrade->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('sessiongrades.view')
                            <button type="button" class="btn btn-sm btn-primary" title="View"
                            data-bs-toggle="modal" data-bs-target="#sessionGradeModal"
                            data-url="{{ route('session-grades.show', $sessionGrade) }}">
                            <i class="bi bi-eye"></i>
                            </button>
                            @endcan
                            @can('sessiongrades.edit')
                                <a href="{{ route('session-grades.edit', $sessionGrade) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('sessiongrades.delete')
                                <form action="{{ route('session-grades.destroy', $sessionGrade) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this session grade?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No session grades found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $sessionGrades->firstItem() ?? 0 }} to {{ $sessionGrades->lastItem() ?? 0 }} of {{ $sessionGrades->total() }} entries</span>
            {{ $sessionGrades->links() }}
        </div>
    </div>
    <div class="modal fade" id="sessionGradeModal" tabindex="-1" aria-labelledby="sessionGradeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionGradeModalTitle">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sessionGradeModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var modal = document.getElementById('sessionGradeModal');
        var body = document.getElementById('sessionGradeModalBody');

        // keep the modal directly under <body> so the layout's containers can't clip it
        document.body.appendChild(modal);

        modal.addEventListener('show.bs.modal', function (event) {
            var url = event.relatedTarget && event.relatedTarget.dataset.url;
            if (!url) return;

            body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-info"></div></div>';

            fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.text();
                })
                .then(function (html) { body.innerHTML = html; })
                .catch(function () {
                    body.innerHTML = '<div class="alert alert-danger mb-0">Could not load the details. Please try again.</div>';
                });
        });
    })();
</script>
@stop
