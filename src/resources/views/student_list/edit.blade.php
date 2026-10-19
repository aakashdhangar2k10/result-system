@extends('adminlte::page')

@section('title', 'Edit Student List')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Edit Student List</h3></div>
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

    <div class="card card-info card-outline">
        <form action="{{ route('student-lists.update', $list) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled
                               value="{{ $semester->course?->shortname }} ({{ $semester->course?->fullname }})">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled
                               value="{{ $semester->batch?->label }}{{ $semester->batch?->branch ? ' – ' . $semester->batch->branch->shortname : '' }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-bold text-sm-end">
                        {{ $semester->course?->structure_type === 'year' ? 'Year' : 'Semester' }}
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" disabled value="{{ \App\Support\Term::base($semester->term?->name) }}">
                    </div>
                </div>

                @include('student_list._students')
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Update</button>
                <a href="{{ route('student-lists.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    @include('student_list._students_script')
@stop