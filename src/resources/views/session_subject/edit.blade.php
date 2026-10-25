@extends('adminlte::page')

@section('title', 'Edit Session Subject')

@section('content_header')
    <h3 class="mb-0">Edit Session Subject</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('session-subjects.update', $sessionSubject) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               value="{{ $sessionSubject->course?->fullname }} - {{ $sessionSubject->course?->duration_label }}" disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control"
                               value="{{ $sessionSubject->batch?->label }}{{ $sessionSubject->batch?->branch ? ' – ' . $sessionSubject->batch->branch->shortname : '' }}" disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">
                        {{ $sessionSubject->course?->structure_type === 'year' ? 'Year' : 'Semester' }}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ $sessionSubject->term_label }}" disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subjects</label>
                    <div class="col-sm-9 pt-2">
                        @include('session_subject._subjects')
                        @error('subject_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @error('subject_ids.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('session-subjects.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('change', function (event) {
            if (event.target.id === 'checkAll') {
                document.querySelectorAll('.subject-check').forEach(function (box) {
                    box.checked = event.target.checked;
                });
            }
        });
    </script>
@stop