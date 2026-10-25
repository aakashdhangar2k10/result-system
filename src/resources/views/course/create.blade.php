@extends('adminlte::page')

@section('title', 'Add Course')

@section('content_header')
    <h3 class="mb-0">Add Course</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('courses.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('course._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('courses.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop