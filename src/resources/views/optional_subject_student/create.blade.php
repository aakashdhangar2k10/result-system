@extends('adminlte::page')

@section('title', 'Add Optional Subject Student')

@section('content_header')
    <div class="row">
        <div class="col-sm-8"><h3 class="mb-0">Add Optional Subject Student</h3></div>
        <div class="col-sm-4 text-end">
            <a href="{{ route('optional-subject-students.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <div class="card card-primary card-outline">
        <form action="{{ route('optional-subject-students.store') }}" method="POST">
            @csrf
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
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject</label>
                    <div class="col-sm-9">
                        <select name="subjectid" id="subjectid" class="form-select @error('subjectid') is-invalid @enderror" required>
                            <option value="">Select Subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->subjectid }}"
                                        data-course="{{ $subject->courseid }}"
                                        data-sem="{{ $subject->semid }}"
                                        @selected(old('subjectid') == $subject->subjectid)>
                                    {{ $subject->subjectname }} ({{ $subject->subjectcode }})
                                </option>
                            @endforeach
                        </select>
                        @error('subjectid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Only subjects of an "Optional" subject type are listed.
                            Tick Optional on a type under Configuration → Subject Type.
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Students</label>
                    <div class="col-sm-9 pt-2">
                        <div id="studentsBox" data-url="{{ route('optional-subject-students.options') }}"></div>
                        @error('students') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('optional-subject-students.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    {{-- course -> sessions and semesters --}}
    @include('semester._cascade')
    @include('optional_subject_student._script')

    <script>
        (function () {
            var course = document.getElementById('courseid');
            var batch = document.getElementById('batchid');
            var term = document.getElementById('semid');
            var subject = document.getElementById('subjectid');
            var box = document.getElementById('studentsBox');

            var restore = @json(old('subjectid') !== null);
            var oldStudents = @json(array_values((array) old('students', [])));
            var firstLoad = true;

            var hint = '<div class="text-muted">Select a course, a session, a semester and a subject to load the students.</div>';

            // subjects of the selected course and semester only
            function syncSubjects() {
                Array.prototype.forEach.call(subject.options, function (o) {
                    if (!o.value) return;
                    var show = course.value !== '' && term.value !== ''
                        && o.dataset.course === course.value && o.dataset.sem === term.value;
                    o.hidden = !show;
                    o.disabled = !show;
                });

                var chosen = subject.options[subject.selectedIndex];
                if (chosen && chosen.disabled) subject.value = '';
            }

            function load() {
                if (!batch.value || !subject.value) {
                    box.innerHTML = hint;
                    return;
                }

                var params = new URLSearchParams({ batchid: batch.value, subjectid: subject.value });

                if (firstLoad && restore) {
                    params.append('restore', '1');
                    oldStudents.forEach(function (id) { params.append('selected[]', id); });
                }
                firstLoad = false;

                box.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-info"></div></div>';

                fetch(box.dataset.url + '?' + params.toString(), {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error(response.status);
                        return response.text();
                    })
                    .then(function (html) {
                        box.innerHTML = html;
                        if (window.refreshStudentSummary) window.refreshStudentSummary();
                    })
                    .catch(function () {
                        box.innerHTML = '<div class="alert alert-danger mb-0">Could not load the students. Please try again.</div>';
                    });
            }

            function changed() { syncSubjects(); load(); }

            course.addEventListener('change', changed);
            term.addEventListener('change', changed);
            batch.addEventListener('change', load);
            subject.addEventListener('change', load);

            syncSubjects();
            load();
        })();
    </script>
@stop