@extends('adminlte::page')

@section('title', 'Add Student List')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Add Student List</h3></div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('student-lists.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Back</a>
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

    @php
        // what was ticked before a validation error, so the page can restore it
        $oldExtra = collect((array) old('extra_ids', []))->mapWithKeys(fn ($id) => [$id => [
            'type' => old("extra_type.{$id}", 'back'),
            'remark' => old("extra_remark.{$id}"),
        ]])->all();
    @endphp

    <div class="card card-info card-outline">
        <form action="{{ route('student-lists.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <select name="courseid" id="courseid" class="form-select" required>
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(old('courseid') == $course->courseid)>
                                    {{ $course->fullname }}
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
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Semester</label>
                    <div class="col-sm-9">
                        <select name="semesterid" id="semesterid" class="form-select @error('semesterid') is-invalid @enderror" required>
                            <option value="">Select Semester</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->semesterid }}"
                                        data-course="{{ $semester->courseid }}"
                                        data-batch="{{ $semester->batchid }}"
                                        @selected(old('semesterid') == $semester->semesterid)>
                                    {{ \App\Support\Term::base($semester->term?->name) }} – {{ $semester->semestermonth?->format('F-Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('semesterid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Only semesters that have a session and no student list yet are shown.
                            Add the semester first under <strong>Semester</strong> if it is missing.
                        </div>
                    </div>
                </div>

                <div id="studentsBox" data-url="{{ route('student-lists.options') }}"></div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Submit</button>
                <a href="{{ route('student-lists.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    @include('student_list._students_script')

    <script>
        (function () {
            var course = document.getElementById('courseid');
            var batch = document.getElementById('batchid');
            var semester = document.getElementById('semesterid');
            var box = document.getElementById('studentsBox');

            var restore = @json(old('semesterid') !== null);
            var oldHome = @json(array_values((array) old('students', [])));
            var oldExtra = @json((object) $oldExtra);
            var firstLoad = true;

            var hint = '<div class="row mb-3"><div class="col-sm-9 offset-sm-3 text-muted">'
                + 'Select a course, a session and a semester to load the students.</div></div>';

            function hide(select, keep) {
                Array.prototype.forEach.call(select.options, function (o) {
                    if (!o.value) return;
                    var show = keep(o);
                    o.hidden = !show;
                    o.disabled = !show;
                });

                var chosen = select.options[select.selectedIndex];
                if (chosen && chosen.disabled) select.value = '';
            }

            function sync() {
                // sessions of the selected course
                hide(batch, function (o) { return course.value !== '' && o.dataset.course === course.value; });

                // semesters of the selected course and session
                hide(semester, function (o) {
                    return course.value !== '' && o.dataset.course === course.value
                        && (batch.value === '' || o.dataset.batch === batch.value);
                });
            }

            function load() {
                if (!semester.value) {
                    box.innerHTML = hint;
                    return;
                }

                var params = new URLSearchParams({ semesterid: semester.value });

                if (firstLoad && restore) {
                    params.append('restore', '1');
                    oldHome.forEach(function (id) { params.append('selected[]', id); });
                    Object.keys(oldExtra).forEach(function (id) {
                        params.append('extra[' + id + ']', oldExtra[id].type || 'back');
                        params.append('remark[' + id + ']', oldExtra[id].remark || '');
                    });
                }
                firstLoad = false;

                box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info"></div></div>';

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
                        if (window.refreshStudentCounts) window.refreshStudentCounts();
                    })
                    .catch(function () {
                        box.innerHTML = '<div class="alert alert-danger">Could not load the students. Please try again.</div>';
                    });
            }

            course.addEventListener('change', function () { sync(); load(); });
            batch.addEventListener('change', function () { sync(); load(); });
            semester.addEventListener('change', load);

            sync();
            load();
        })();
    </script>
@stop