@extends('adminlte::page')

@section('title', 'Edit Course')

@section('content_header')
    <h3 class="mb-0">Edit Course</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('courses.update', $course) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('course._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('courses.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop