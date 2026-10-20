@extends('adminlte::page')

@section('title', 'Result')

@section('content_header')
<div class="row">
    <div class="col-sm-6">
        <h3 class="mb-0">Result</h3>
    </div>
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

@php $colors = \App\Services\SemesterResultService::COLORS; @endphp

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
                            @selected(request('courseid')==$course->courseid)>
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
                            @selected(request('batchid')==$batch->batchid)>
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
                            @selected(request('semid')==$term->semid)>
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
                            @selected(request('semesterexamid')==$e->semesterexamid)>
                            {{ $e->exam_label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label fw-bold d-block">View Type</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="view" id="viewGrades" value="grades" @checked($view==='grades' )>
                        <label class="form-check-label" for="viewGrades">Grades</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="view" id="viewMarks" value="marks" @checked($view==='marks' )>
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

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <input type="search" id="sheetSearch" class="form-control form-control-sm w-auto" placeholder="Search name or roll no.">

            @can('semesterresults.calculate')
            @if ($results->isNotEmpty())
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sgpaModal">
                <i class="bi bi-calculator"></i> {{ $semResult ? 'Recalculate SGPA' : 'Calculate SGPA' }}
            </button>
            @endif
            @endcan

            @can('semesterresults.download')
            @if ($results->isNotEmpty())
            @if ($semResult && ! $outdated)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#downloadModal">
                <i class="bi bi-download"></i> Download
            </button>
            @else
            <span class="d-inline-block" tabindex="0"
                title="{{ $semResult ? 'Recalculate SGPA first: marks or the rule changed.' : 'Calculate SGPA first.' }}">
                <button type="button" class="btn btn-primary btn-sm" disabled style="pointer-events:none">
                    <i class="bi bi-download"></i> Download
                </button>
            </span>
            @endif
            @endif
            @endcan
        </div>
    </div>

    {{-- the pass / fail rule of this course and session --}}
    @if ($rule)
    <div class="card-body border-bottom py-2 small">
        <strong>Result rule:</strong>
        @foreach ($rule->rule_lines as $line)
        <span class="badge text-bg-{{ $line['color'] }}">{{ $line['label'] }}</span> {{ $line['text'] }}@if (! $loop->last) · @endif
        @endforeach
        <span class="text-muted">· failed subjects {{ $rule->sgpa_with_failed ? 'count in' : 'are left out of' }} the SGPA</span>
    </div>
    @else
    <div class="alert alert-warning rounded-0 mb-0">
        <i class="bi bi-exclamation-triangle-fill"></i>
        No result rule is set for this session. Add it under <strong>Course Session → Session Grade</strong>.
    </div>
    @endif

    {{-- SGPA state --}}
    @if ($semResult)
    <div class="card-body border-bottom py-2 small d-flex flex-wrap gap-3 align-items-center">
        <span>
            <i class="bi bi-calculator"></i> SGPA calculated {{ $semResult->calculated_at?->format('d M Y, h:i A') }}
            by {{ $semResult->calculator?->name ?? '—' }}
        </span>
        <span>
            {{ $semResult->pass_label }} {{ $semResult->pass_count }} ·
            {{ $semResult->promoted_label }} {{ $semResult->promoted_count }} ·
            {{ $semResult->fail_label }} {{ $semResult->fail_count }}
        </span>
        @if ($outdated)
        <span class="badge text-bg-warning">
            Marks or the rule changed after this calculation: click Recalculate SGPA
        </span>
        @endif
    </div>
    @elseif ($results->isNotEmpty())
    <div class="card-body border-bottom py-2 small text-muted">
        SGPA is not calculated yet. Add the result of every subject, then click Calculate SGPA.
    </div>
    @endif

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
                                data-title="{{ $r->subject?->subjectname }}"
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
                    <th class="text-center">SGPA</th>
                    <th class="text-center">Result</th>
                    <th class="text-center" style="width:70px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                @php $sg = $sgpaItems->get($student->studentid); @endphp
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

                    <td class="text-center fw-bold">{{ $sg ? number_format((float) $sg->sgpa, 2) : '—' }}</td>
                    <td class="text-center">
                        @if ($sg)
                        <span class="badge text-bg-{{ $colors[$sg->status] ?? 'secondary' }}">{{ $sg->result_label }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-success" title="View result of this student"
                            data-bs-toggle="modal" data-bs-target="#resultModal"
                            data-title="{{ $student->full_name }}"
                            data-url="{{ route('results.student', [$exam, $student]) }}">
                            <i class="bi bi-search"></i>
                        </button>
                    </td>
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
                    <th colspan="3" class="text-center">
                        @if ($semResult)
                        {{ $semResult->pass_label }} {{ $semResult->pass_count }} ·
                        {{ $semResult->promoted_label }} {{ $semResult->promoted_count }} ·
                        {{ $semResult->fail_label }} {{ $semResult->fail_count }}
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif


    @can('semesterresults.download')
    @if ($semResult && ! $outdated)
    <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="GET" action="{{ route('results.download', $exam) }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalTitle">Download Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        {{ $exam->course?->shortname }} · {{ $exam->batch?->label }}
                        · {{ \App\Support\Term::base($exam->term?->name) }} · {{ $exam->exam_label }}
                        — {{ $students->count() }} students, {{ $results->count() }} subjects
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">File format</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="format" id="fmtPdf" value="pdf" checked>
                            <label class="btn btn-outline-danger" for="fmtPdf"><i class="bi bi-file-earmark-pdf"></i> PDF</label>

                            <input type="radio" class="btn-check" name="format" id="fmtXlsx" value="xlsx">
                            <label class="btn btn-outline-success" for="fmtXlsx"><i class="bi bi-file-earmark-excel"></i> Excel (.xlsx)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Show in the subject columns</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="view" id="dlGrades" value="grades" @checked($view==='grades' )>
                            <label class="btn btn-outline-primary" for="dlGrades">Grades</label>

                            <input type="radio" class="btn-check" name="view" id="dlMarks" value="marks" @checked($view==='marks' )>
                            <label class="btn btn-outline-primary" for="dlMarks">Marks</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label fw-bold" for="dlSort">Order students by</label>
                            <select name="sort" id="dlSort" class="form-select">
                                @foreach (\App\Services\ResultExportService::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($value==='roll' )>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-bold d-block">Direction</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="dir" id="dlAsc" value="asc" checked>
                                <label class="btn btn-outline-secondary" for="dlAsc" title="A to Z, lowest first"><i class="bi bi-sort-down-alt"></i> Asc</label>

                                <input type="radio" class="btn-check" name="dir" id="dlDesc" value="desc">
                                <label class="btn btn-outline-secondary" for="dlDesc" title="Z to A, highest first"><i class="bi bi-sort-up"></i> Desc</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">Descending SGPA puts the highest SGPA first. Equal values are ordered by roll number.</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    {{-- the download starts and the popup closes --}}
                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="bi bi-download"></i> Download
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            var modal = document.getElementById('downloadModal');

            // keep the modal directly under <body> so the layout's containers can't clip it
            document.body.appendChild(modal);

            // SGPA is usually read highest first, everything else A to Z
            modal.querySelector('#dlSort').addEventListener('change', function(event) {
                document.getElementById(event.target.value === 'sgpa' ? 'dlDesc' : 'dlAsc').checked = true;
            });
        })();
    </script>
    @endif
    @endcan
</div>

{{-- Calculate SGPA: progress popup --}}
@can('semesterresults.calculate')
<div class="modal fade" id="sgpaModal" tabindex="-1" aria-labelledby="sgpaModalTitle" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false"
    data-url="{{ route('results.sgpa', $exam) }}" data-token="{{ csrf_token() }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title" id="sgpaModalTitle">Calculating SGPA</h5>
            </div>

            <div class="modal-body">
                <div id="sgpaWorking">
                    <div class="spinner-border text-primary mb-2"></div>
                    <div class="text-primary">Loading...</div>
                    <div>Please wait while SGPA is being calculated...</div>
                    <div class="mt-2 text-muted">{{ $students->count() }} students</div>
                </div>

                <div id="sgpaDone" class="d-none">
                    <div class="display-3 text-success lh-1 mb-2">✓</div>
                    <div class="fw-bold" id="sgpaDoneText"></div>
                    <div class="text-muted mt-1" id="sgpaSummary"></div>
                </div>

                <div id="sgpaFail" class="d-none text-start">
                    <div class="alert alert-danger mb-0">
                        <div class="fw-bold mb-1">SGPA cannot be calculated yet</div>
                        <ul class="mb-0 ps-3" id="sgpaFailList"></ul>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-center d-none" id="sgpaFooter">
                <button type="button" class="btn btn-success" id="sgpaClose" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
@endcan
@elseif (request()->hasAny(['courseid', 'batchid', 'semid']))
<div class="alert alert-info">Select the exam to view its result.</div>
@endif

{{-- subject and student popup --}}
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalTitle">Result</h5>
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
    (function() {
        var course = document.getElementById('courseid');
        var batch = document.getElementById('batchid');
        var term = document.getElementById('semid');
        var exam = document.getElementById('examid');

        // exams of the selected course, session and semester
        function syncExams() {
            Array.prototype.forEach.call(exam.options, function(o) {
                if (!o.value) return;
                var show = course.value !== '' && o.dataset.course === course.value &&
                    (!batch.value || o.dataset.batch === batch.value) &&
                    (!term.value || o.dataset.sem === term.value);
                o.hidden = !show;
                o.disabled = !show;
            });

            var chosen = exam.options[exam.selectedIndex];
            if (chosen && chosen.disabled) exam.value = '';
        }

        [course, batch, term].forEach(function(el) {
            el.addEventListener('change', syncExams);
        });
        syncExams();

        // search inside the result sheet
        var search = document.getElementById('sheetSearch');
        if (search) {
            search.addEventListener('input', function() {
                var q = search.value.trim().toLowerCase();
                document.querySelectorAll('#sheetTable tbody tr').forEach(function(row) {
                    row.style.display = row.dataset.search.indexOf(q) !== -1 ? '' : 'none';
                });
            });
        }

        // subject and student popup
        var modal = document.getElementById('resultModal');
        var body = document.getElementById('resultModalBody');

        // keep the modals directly under <body> so the layout's containers can't clip them
        document.body.appendChild(modal);

        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var url = button && button.dataset.url;
            if (!url) return;

            document.getElementById('resultModalTitle').textContent = button.dataset.title || 'Result';
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

        // Calculate SGPA popup
        var sgpa = document.getElementById('sgpaModal');

        if (sgpa) {
            document.body.appendChild(sgpa);

            var parts = {
                working: document.getElementById('sgpaWorking'),
                done: document.getElementById('sgpaDone'),
                fail: document.getElementById('sgpaFail')
            };
            var footer = document.getElementById('sgpaFooter');
            var closeBtn = document.getElementById('sgpaClose');

            function show(state) {
                Object.keys(parts).forEach(function(key) {
                    parts[key].classList.toggle('d-none', key !== state);
                });
                footer.classList.toggle('d-none', state === 'working');
                closeBtn.textContent = state === 'done' ? 'Done' : 'Close';
            }

            sgpa.addEventListener('show.bs.modal', function() {
                sgpa.dataset.reload = '0';
                show('working');

                fetch(sgpa.dataset.url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': sgpa.dataset.token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            return {
                                ok: response.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(res) {
                        if (res.ok && res.data.ok) {
                            document.getElementById('sgpaDoneText').textContent = res.data.message;
                            document.getElementById('sgpaSummary').textContent = res.data.summary || '';
                            sgpa.dataset.reload = '1';
                            show('done');
                            return;
                        }

                        var list = document.getElementById('sgpaFailList');
                        list.innerHTML = '';

                        (res.data.errors || [res.data.message || 'Something went wrong.']).forEach(function(message) {
                            var li = document.createElement('li');
                            li.textContent = message;
                            list.appendChild(li);
                        });

                        show('fail');
                    })
                    .catch(function() {
                        var list = document.getElementById('sgpaFailList');
                        list.innerHTML = '<li>Could not calculate. Reload the page and try again.</li>';
                        show('fail');
                    });
            });

            // Done reloads the sheet, so the new SGPA and result show
            sgpa.addEventListener('hidden.bs.modal', function() {
                if (sgpa.dataset.reload === '1') window.location.reload();
            });
        }
    })();
</script>
@stop