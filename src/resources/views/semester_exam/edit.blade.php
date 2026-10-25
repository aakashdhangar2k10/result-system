@extends('adminlte::page')

@section('title', 'Edit Semester Exam')

@section('content_header')
    <h3 class="mb-0">Edit Semester Exam</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('semester-exams.update', $semesterExam) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('semester_exam._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('semester-exams.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop