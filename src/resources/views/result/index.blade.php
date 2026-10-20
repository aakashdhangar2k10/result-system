@extends('adminlte::page')

@section('title', 'Result')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Result</h3></div>
        <div class="col-sm-6 text-end">
            @can('subjectresults.create')
                <a href="{{ route('results.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
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
        <form method="GET" action="{{ route('results.index') }}">
            <div class="card-header">
                <h3 class="card-title mb-0">Select criteria for view result</h3>
            </div>

            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" id="courseid" class="form-select">
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}"
                                        data-terms="{{ $course->total_terms }}"
                                        data-structure="{{ $course->structure_type }}"
                                        @selected(request('courseid') == $course->courseid)>
                                    {{ $course->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold">Session</label>
                        <select name="batchid" id="batchid" class="form-select">
                            <option value="">Select Session</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                                    @selected(request('batchid') == $batch->batchid)>
                                    {{ $batch->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold" id="term_label">Semester</label>
                        <select name="semid" id="semid" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->semid }}"
                                        data-number="{{ \App\Support\Term::number($term->roman) }}"
                                        data-base="{{ \App\Support\Term::base($term->name) }}"
                                        @selected(request('semid') == $term->semid)>
                                    {{ \App\Support\Term::label($term->name, 'semester') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label fw-bold">Exam</label>
                        <select name="semesterexamid" id="examid" class="form-select" required>
                            <option value="">Select Exam</option>
                            @foreach ($exams as $e)
                                <option value="{{ $e->semesterexamid }}"
                                        data-course="{{ $e->courseid }}" data-batch="{{ $e->batchid }}" data-sem="{{ $e->semid }}"
                                        @selected(request('semesterexamid') == $e->semesterexamid)>
                                    {{ $e->exam_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold d-block">View Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="view" id="viewGrades" value="grades" @checked($view === 'grades')>
                            <label class="form-check-label" for="viewGrades">Grades</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="view" id="viewMarks" value="marks" @checked($view === 'marks')>
                            <label class="form-check-label" for="viewMarks">Marks</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-info text-white">Search</button>
                    <a href="{{ route('results.index') }}" class="btn btn-light border">Reset</a>
                </div>
            </div>
        </form>
    </div>

    @if ($exam)
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>{{ $exam->course?->fullname }}</strong>
                    · {{ $exam->batch?->label }}
                    · {{ \App\Support\Term::base($exam->term?->name) }}
                    · {{ $exam->exam_label }}
                    <span class="badge text-bg-light border ms-2">{{ $students->count() }} students, {{ $results->count() }} subjects</span>
                </div>
                <input type="search" id="sheetSearch" class="form-control form-control-sm w-auto" placeholder="Search name or roll no.">
            </div>

            @if ($missing->isNotEmpty())
                <div class="alert alert-warning rounded-0 mb-0">
                    <i class="bi bi-hourglass-split"></i>
                    Result not added yet for:
                    {{ $missing->map(fn ($s) => $s->subjectname . ' (' . $s->subjectcode . ')')->implode(', ') }}
                </div>
            @endif

            @if ($results->isEmpty())
                <div class="card-body text-center text-muted py-4">No result has been added for this exam yet.</div>
            @else
                <div class="card-body p-0 table-responsive" style="max-height:640px">
                    <table class="table table-sm table-bordered table-striped align-middle mb-0" id="sheetTable">
                        <thead class="sticky-top bg-body">
                            <tr>
                                <th style="width:60px">Sr. No</th>
                                <th>Roll No.</th>
                                <th>Student Name</th>
                                @foreach ($results as $r)
                                    <th class="text-center" style="min-width:150px">
                                        <div>{{ $r->subject?->subjectcode }}</div>
                                        <div class="small fw-normal">{{ \Illuminate\Support\Str::limit($r->subject?->subjectname, 30) }}</div>
                                        <div class="mt-1 text-nowrap">
                                            <button type="button" class="btn btn-xs btn-success py-0 px-1" title="View"
                                                    data-bs-toggle="modal" data-bs-target="#resultModal"
                                                    data-url="{{ route('results.show', $r) }}">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            @can('subjectresults.edit')
                                                <a href="{{ route('results.edit', $r) }}" class="btn btn-xs btn-info text-white py-0 px-1" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endcan
                                            @can('subjectresults.delete')
                                                <form action="{{ route('results.destroy', $r) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete the result of {{ e($r->subject?->subjectname) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-xs btn-danger py-0 px-1" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($students as $student)
                            <tr data-search="{{ mb_strtolower($student->rollno . ' ' . $student->full_name) }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student->rollno }}</td>
                                <td>{{ $student->full_name }}</td>
                                @foreach ($results as $r)
                                    @php
                                        $item = $cells[$student->studentid][$r->resultid] ?? null;
                                        $tone = $item ? ($item->is_absent ? 'secondary' : ($item->is_pass ? 'success' : 'danger')) : null;
                                    @endphp
                                    <td class="text-center">
                                        @if (! $item)
                                            <span class="text-muted">—</span>
                                        @elseif ($view === 'grades')
                                            <span class="badge text-bg-{{ $tone }}">{{ $item->grade }}</span>
                                        @elseif ($item->is_absent)
                                            <span class="text-secondary fw-bold">Ab</span>
                                        @elseif ($item->total !== null)
                                            <span class="{{ $item->is_pass ? '' : 'text-danger fw-bold' }}">{{ \App\Services\ResultService::fmt($item->total) }}</span>
                                        @else
                                            <span class="badge text-bg-{{ $tone }}">{{ $item->grade }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="bg-body small">
                            <tr>
                                <th colspan="3" class="text-end">Passed / Failed / Absent</th>
                                @foreach ($results as $r)
                                    @php $s = $stats[$r->resultid] ?? ['pass' => 0, 'fail' => 0, 'absent' => 0]; @endphp
                                    <th class="text-center">
                                        <span class="text-success">{{ $s['pass'] }}</span> /
                                        <span class="text-danger">{{ $s['fail'] }}</span> /
                                        <span class="text-secondary">{{ $s['absent'] }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @elseif (request()->hasAny(['courseid', 'batchid', 'semid']))
        <div class="alert alert-info">Select the exam to view its result.</div>
    @endif

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalTitle">Subject Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- course -> sessions and semesters --}}
    @include('semester._cascade')

    <script>
        (function () {
            var course = document.getElementById('courseid');
            var batch = document.getElementById('batchid');
            var term = document.getElementById('semid');
            var exam = document.getElementById('examid');

            // exams of the selected course, session and semester
            function syncExams() {
                Array.prototype.forEach.call(exam.options, function (o) {
                    if (!o.value) return;
                    var show = course.value !== '' && o.dataset.course === course.value
                        && (!batch.value || o.dataset.batch === batch.value)
                        && (!term.value || o.dataset.sem === term.value);
                    o.hidden = !show;
                    o.disabled = !show;
                });

                var chosen = exam.options[exam.selectedIndex];
                if (chosen && chosen.disabled) exam.value = '';
            }

            [course, batch, term].forEach(function (el) { el.addEventListener('change', syncExams); });
            syncExams();

            // search inside the result sheet
            var search = document.getElementById('sheetSearch');
            if (search) {
                search.addEventListener('input', function () {
                    var q = search.value.trim().toLowerCase();
                    document.querySelectorAll('#sheetTable tbody tr').forEach(function (row) {
                        row.style.display = row.dataset.search.indexOf(q) !== -1 ? '' : 'none';
                    });
                });
            }

            // View popup of one subject
            var modal = document.getElementById('resultModal');
            var body = document.getElementById('resultModalBody');

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