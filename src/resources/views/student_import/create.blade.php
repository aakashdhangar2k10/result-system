@extends('adminlte::page')

@section('title', 'Add Student With Excel')

@section('content_header')
    <h3 class="mb-0">Add Student With Excel</h3>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nothing is saved yet. The file is checked first, then you see a preview with the number of valid rows
        and the errors. The students are saved only after you confirm.
    </div>

    <div class="card card-primary card-outline">
        <form action="{{ route('student-imports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(old('courseid') == $course->courseid)>
                                    {{ $course->fullname }}
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

                <h5 class="mt-4 mb-3">Import Student From Excel</h5>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Add Excel File</label>
                    <div class="col-sm-9">
                        <input type="file" name="file" accept=".xlsx,.xls"
                               class="form-control @error('file') is-invalid @enderror" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            .xlsx or .xls, up to 2 MB and {{ \App\Services\StudentImportService::MAX_ROWS }} students.
                            Roll No., Enrollment No., First Name, Father Name and Mother Name are required.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('student-imports.template') }}" id="templateLink">
                    <i class="bi bi-download"></i> Download Excel Template
                </a>
                <div>
                    <button type="submit" class="btn btn-info text-white">Upload &amp; Preview</button>
                    <button type="reset" class="btn btn-light border">Reset</button>
                    <a href="{{ route('students.index') }}" class="btn btn-danger">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var course = document.getElementById('courseid');
            var batch = document.getElementById('batchid');
            var link = document.getElementById('templateLink');
            var base = link.getAttribute('href');

            function sync() {
                // sessions of the selected course only
                Array.prototype.forEach.call(batch.options, function (o) {
                    if (!o.value) return;
                    var match = course.value !== '' && o.dataset.course === course.value;
                    o.hidden = !match;
                    o.disabled = !match;
                });

                var chosen = batch.options[batch.selectedIndex];
                if (chosen && chosen.disabled) batch.value = '';

                // the template gets the school and course name of the selected course
                link.setAttribute('href', course.value ? base + '?courseid=' + encodeURIComponent(course.value) : base);
            }

            course.addEventListener('change', sync);
            sync();
        })();
    </script>
@stop