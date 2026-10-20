@extends('adminlte::page')

@section('title', 'Edit Result')

@section('content_header')
    <h3 class="mb-0">Result</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @php
        $f = fn ($value) => \App\Services\ResultService::fmt($value);
        $mode = $entry['mode'] ?? $result->entry_mode;
    @endphp

    <form id="resultForm" method="POST" action="{{ route('results.update', $result) }}"
          data-calc-url="{{ route('results.calculate') }}" data-entry-url="{{ route('results.entry') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="result_id" value="{{ $result->resultid }}">
        <input type="hidden" name="semesterexamid" value="{{ $result->semesterexamid }}">
        <input type="hidden" name="subjectid" value="{{ $result->subjectid }}">

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-info card-outline">
                    <div class="card-header"><h3 class="card-title">Edit Result</h3></div>
                    <div class="card-body">
                        @foreach ([
                            'Course' => $result->course?->fullname . ' - ' . $result->course?->duration_label,
                            'Session' => $result->batch?->label . ($result->batch?->branch ? ' – ' . $result->batch->branch->shortname : ''),
                            ($result->course?->structure_type === 'year' ? 'Year' : 'Semester') => \App\Support\Term::base($result->term?->name),
                            'Attempt' => $result->exam?->exam_label,
                            'Subject' => $result->subject?->subjectname . ' (' . $result->subject?->subjectcode . ')',
                        ] as $label => $value)
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label fw-bold text-sm-end">{{ $label }}</label>
                                <div class="col-sm-9"><input type="text" class="form-control" value="{{ $value }}" disabled></div>
                            </div>
                        @endforeach

                        <div class="row">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Entry Type</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="entry_mode" id="modeMarks" value="marks"
                                           @checked($mode === 'marks') @disabled(! $canMarks)>
                                    <label class="form-check-label" for="modeMarks">Enter Marks</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="entry_mode" id="modeGrade" value="grade"
                                           @checked($mode === 'grade')>
                                    <label class="form-check-label" for="modeGrade">Enter Grade</label>
                                </div>
                                <div class="form-text">Switching the entry type clears the values of the other type.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-info card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Subject Detail</h3>
                        <a href="{{ route('results.index', ['courseid' => $result->courseid, 'batchid' => $result->batchid, 'semid' => $result->semid, 'semesterexamid' => $result->semesterexamid]) }}"
                           class="btn btn-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <tr><th style="width:45%">Subject Code</th><td>{{ $result->subject?->subjectcode }}</td></tr>
                            <tr><th>Subject Credit</th><td>{{ $f($result->subject_credit) }}</td></tr>
                            <tr><th>Max Internal Marks</th><td>{{ $result->max_internal }}</td></tr>
                            <tr><th>Max External Marks</th><td>{{ $result->max_external }}</td></tr>
                            <tr><th>Total Marks</th><td>{{ $result->max_internal + $result->max_external }}</td></tr>
                            <tr><th>Subject Type</th><td>{{ $result->subject?->type_label }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-info card-outline">
            <div class="card-body" id="entryBox">
                @include('result._entry', $entry)
            </div>
            <div class="card-footer text-end">
                <span id="calcMessage" class="me-3"></span>
                <button type="button" id="calcBtn" class="btn btn-primary" disabled>Calculate Grade</button>
                <button type="submit" id="submitBtn" class="btn btn-info text-white" disabled>Update</button>
                <a href="{{ route('results.index', ['courseid' => $result->courseid, 'batchid' => $result->batchid, 'semid' => $result->semid, 'semesterexamid' => $result->semesterexamid]) }}"
                   class="btn btn-danger">Cancel</a>
            </div>
        </div>
    </form>

    @include('result._script')

    <script>
        (function () {
            var form = document.getElementById('resultForm');
            var marks = document.getElementById('modeMarks');
            var grade = document.getElementById('modeGrade');
            var box = document.getElementById('entryBox');

            // switching between marks and grade reloads the table
            function load() {
                var params = new URLSearchParams({
                    semesterexamid: form.querySelector('[name=semesterexamid]').value,
                    subjectid: form.querySelector('[name=subjectid]').value,
                    result: form.querySelector('[name=result_id]').value,
                    mode: grade.checked ? 'grade' : 'marks'
                });

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

            marks.addEventListener('change', load);
            grade.addEventListener('change', load);
        })();
    </script>
@stop