@extends('adminlte::page')

@section('title', 'Add Result')

@section('content_header')
    <h3 class="mb-0">Result</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form id="resultForm" method="POST" action="{{ route('results.store') }}"
          data-calc-url="{{ route('results.calculate') }}" data-entry-url="{{ route('results.entry') }}">
        @csrf

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">Add Result</h3></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                            <div class="col-sm-9">
                                <select name="courseid" id="courseid" class="form-select" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->courseid }}"
                                                data-terms="{{ $course->total_terms }}"
                                                data-structure="{{ $course->structure_type }}"
                                                @selected(old('courseid') == $course->courseid)>
                                            {{ $course->fullname }} - {{ $course->duration_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
                            <div class="col-sm-9">
                                <select name="batchid" id="batchid" class="form-select" required>
                                    <option value="">Select Session</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                                            @selected(old('batchid') == $batch->batchid)>
                                            {{ $batch->label }}{{ $batch->branch ? ' – ' . $batch->branch->shortname : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end" id="term_label">Semester</label>
                            <div class="col-sm-9">
                                <select name="semid" id="semid" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->semid }}"
                                                data-number="{{ \App\Support\Term::number($term->roman) }}"
                                                data-base="{{ \App\Support\Term::base($term->name) }}"
                                                @selected(old('semid') == $term->semid)>
                                            {{ \App\Support\Term::label($term->name, 'semester') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Attempt</label>
                            <div class="col-sm-9">
                                <select name="semesterexamid" id="examid" class="form-select" required>
                                    <option value="">Select Attempt</option>
                                    @foreach ($exams as $exam)
                                        <option value="{{ $exam->semesterexamid }}"
                                                data-course="{{ $exam->courseid }}"
                                                data-batch="{{ $exam->batchid }}"
                                                data-sem="{{ $exam->semid }}"
                                                @selected(old('semesterexamid') == $exam->semesterexamid)>
                                            {{ $exam->exam_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject</label>
                            <div class="col-sm-9">
                                <select name="subjectid" id="subjectid" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->subjectid }}"
                                                data-course="{{ $subject->courseid }}"
                                                data-sem="{{ $subject->semid }}"
                                                data-code="{{ $subject->subjectcode }}"
                                                data-credit="{{ (float) $subject->subjectcredit }}"
                                                data-internal="{{ $subject->maxinternalmarks }}"
                                                data-external="{{ $subject->maxexternalmarks }}"
                                                data-type="{{ $subject->type_label }}"
                                                data-addgrade="{{ $subject->add_grade ? 1 : 0 }}"
                                                @selected(old('subjectid') == $subject->subjectid)>
                                            {{ $subject->subjectname }} ({{ $subject->subjectcode }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Entry Type</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="entry_mode" id="modeMarks" value="marks"
                                           @checked(old('entry_mode', 'marks') === 'marks')>
                                    <label class="form-check-label" for="modeMarks">Enter Marks</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="entry_mode" id="modeGrade" value="grade"
                                           @checked(old('entry_mode') === 'grade')>
                                    <label class="form-check-label" for="modeGrade">Enter Grade</label>
                                </div>
                                <div class="form-text">Subjects graded directly, or without marks, use Enter Grade.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-primary card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Subject Detail</h3>
                        <a href="{{ route('results.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <tr><th style="width:45%">Subject Code</th><td id="d_code">—</td></tr>
                            <tr><th>Subject Credit</th><td id="d_credit">—</td></tr>
                            <tr><th>Max Internal Marks</th><td id="d_internal">—</td></tr>
                            <tr><th>Max External Marks</th><td id="d_external">—</td></tr>
                            <tr><th>Total Marks</th><td id="d_total">—</td></tr>
                            <tr><th>Subject Type</th><td id="d_type">—</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-body" id="entryBox"></div>
            <div class="card-footer text-end">
                <span id="calcMessage" class="me-3"></span>
                <button type="button" id="calcBtn" class="btn btn-primary" disabled>Calculate Grade</button>
                <button type="submit" id="submitBtn" class="btn btn-info text-white" disabled>Submit</button>
                <a href="{{ route('results.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </div>
    </form>

    {{-- course -> sessions and semesters --}}
    @include('semester._cascade')
    @include('result._script')

    <script>
        (function () {
            var form = document.getElementById('resultForm');
            var course = document.getElementById('courseid');
            var batch = document.getElementById('batchid');
            var term = document.getElementById('semid');
            var exam = document.getElementById('examid');
            var subject = document.getElementById('subjectid');
            var marks = document.getElementById('modeMarks');
            var grade = document.getElementById('modeGrade');
            var box = document.getElementById('entryBox');

            var hint = '<div class="text-muted">Select the course, session, semester, attempt and subject to load the students.</div>';

            function narrow(select, keep) {
                Array.prototype.forEach.call(select.options, function (o) {
                    if (!o.value) return;
                    var show = keep(o);
                    o.hidden = !show;
                    o.disabled = !show;
                });

                var chosen = select.options[select.selectedIndex];
                if (chosen && chosen.disabled) select.value = '';
            }

            // attempts and subjects of the selected course, session and semester
            function syncLists() {
                narrow(exam, function (o) {
                    return course.value !== '' && o.dataset.course === course.value
                        && (!batch.value || o.dataset.batch === batch.value)
                        && (!term.value || o.dataset.sem === term.value);
                });

                narrow(subject, function (o) {
                    return course.value !== '' && term.value !== ''
                        && o.dataset.course === course.value && o.dataset.sem === term.value;
                });
            }

            function currentSubject() {
                var opt = subject.options[subject.selectedIndex];
                return opt && opt.value ? opt : null;
            }

            function marksAllowed(opt) {
                return !!opt && opt.dataset.addgrade !== '1'
                    && (parseFloat(opt.dataset.internal) + parseFloat(opt.dataset.external)) > 0;
            }

            // the Subject Detail card
            function fillDetail() {
                var opt = currentSubject();
                var set = function (id, text) { document.getElementById(id).textContent = text; };

                set('d_code', opt ? opt.dataset.code : '—');
                set('d_credit', opt ? opt.dataset.credit : '—');
                set('d_internal', opt ? opt.dataset.internal : '—');
                set('d_external', opt ? opt.dataset.external : '—');
                set('d_total', opt ? (parseFloat(opt.dataset.internal) + parseFloat(opt.dataset.external)) : '—');
                set('d_type', opt ? opt.dataset.type : '—');
            }

            // marks entry only for subjects that have marks and are not graded directly
            function syncMode(reset) {
                var opt = currentSubject();
                var allowed = marksAllowed(opt);

                marks.disabled = !!opt && !allowed;

                if (opt && !allowed) grade.checked = true;
                else if (reset) marks.checked = true;
            }

            function load() {
                if (!exam.value || !subject.value) {
                    box.innerHTML = hint;
                    if (window.resultForm) window.resultForm.afterLoad();
                    return;
                }

                var mode = grade.checked ? 'grade' : 'marks';
                var params = new URLSearchParams({ semesterexamid: exam.value, subjectid: subject.value, mode: mode });

                box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info"></div></div>';

                fetch(form.dataset.entryUrl + '?' + params.toString(), {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error(response.status);
                        return response.text();
                    })
                    .then(function (html) {
                        box.innerHTML = html;
                        window.resultForm.afterLoad();
                    })
                    .catch(function () {
                        box.innerHTML = '<div class="alert alert-danger mb-0">Could not load the students. Please try again.</div>';
                    });
            }

            course.addEventListener('change', function () { syncLists(); fillDetail(); syncMode(true); load(); });
            batch.addEventListener('change', function () { syncLists(); load(); });
            term.addEventListener('change', function () { syncLists(); fillDetail(); syncMode(true); load(); });
            exam.addEventListener('change', load);
            subject.addEventListener('change', function () { fillDetail(); syncMode(true); load(); });
            marks.addEventListener('change', load);
            grade.addEventListener('change', load);

            syncLists();
            fillDetail();
            syncMode(false);
            load();
        })();
    </script>
@stop