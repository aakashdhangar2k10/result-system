@extends('adminlte::page')

@section('title', 'Add Session Subject')

@section('content_header')
    <h3 class="mb-0">Add Session Subject</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('session-subjects.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
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
                        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
                    <div class="col-sm-9">
                        <select name="batchid" id="batchid" class="form-select @error('batchid') is-invalid @enderror" required>
                            <option value="">Select Session</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                                    @selected(old('batchid') == $batch->batchid)>
                                    {{ $batch->label }}{{ $batch->branch ? ' – ' . $batch->branch->shortname : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('batchid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end" id="term_label">Semester</label>
                    <div class="col-sm-9">
                        <select name="semid" id="semid" class="form-select @error('semid') is-invalid @enderror" required>
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
                        @error('semid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subjects</label>
                    <div class="col-sm-9 pt-2">
                        <div id="subjectsBox" data-url="{{ route('session-subjects.options') }}"></div>
                        @error('subject_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @error('subject_ids.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('session-subjects.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    {{-- course -> sessions and semesters (shared with the Semester forms) --}}
    @include('semester._cascade')

    <script>
        (function () {
            var course = document.getElementById('courseid');
            var term = document.getElementById('semid');
            var box = document.getElementById('subjectsBox');
            var oldIds = @json(array_map('intval', (array) old('subject_ids', [])));
            var firstLoad = true;

            var hint = '<div class="text-muted">Select a course, session and semester to load the subjects.</div>';

            function load() {
                if (!course.value || !term.value) {
                    box.innerHTML = hint;
                    return;
                }

                var params = new URLSearchParams({ courseid: course.value, semid: term.value });

                // after a validation error keep the previous ticks; otherwise tick everything
                if (firstLoad && oldIds.length) {
                    oldIds.forEach(function (id) { params.append('selected[]', id); });
                } else {
                    params.append('check_all', '1');
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
                    .then(function (html) { box.innerHTML = html; })
                    .catch(function () {
                        box.innerHTML = '<div class="alert alert-danger mb-0">Could not load the subjects. Please try again.</div>';
                    });
            }

            // "Select all" works on content that was loaded later, so it listens on the document
            document.addEventListener('change', function (event) {
                if (event.target.id === 'checkAll') {
                    document.querySelectorAll('.subject-check').forEach(function (box) {
                        box.checked = event.target.checked;
                    });
                }
            });

            course.addEventListener('change', load);
            term.addEventListener('change', load);
            load();
        })();
    </script>
@stop