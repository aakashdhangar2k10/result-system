@extends('adminlte::page')

@section('title', 'Add Student')

@section('content_header')
    <h3 class="mb-0">Add Student</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('student._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('students.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop