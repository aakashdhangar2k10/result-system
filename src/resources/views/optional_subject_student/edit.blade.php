@extends('adminlte::page')

@section('title', 'Edit Optional Subject Student')

@section('content_header')
    <div class="row">
        <div class="col-sm-8"><h3 class="mb-0">Edit Optional Subject Student</h3></div>
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
        <form action="{{ route('optional-subject-students.update', $record) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled
                               value="{{ $record->subject->course?->fullname }} - {{ $record->subject->course?->duration_label }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled
                               value="{{ $record->batch?->label }}{{ $record->batch?->branch ? ' – ' . $record->batch->branch->shortname : '' }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">
                        {{ $record->subject->course?->structure_type === 'year' ? 'Year' : 'Semester' }}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled value="{{ \App\Support\Term::base($record->subject->term?->name) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled
                               value="{{ $record->subject->subjectname }} ({{ $record->subject->subjectcode }})">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Students</label>
                    <div class="col-sm-9 pt-2">
                        @include('optional_subject_student._students')
                        @error('students') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('optional-subject-students.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    @include('optional_subject_student._script')
@stop