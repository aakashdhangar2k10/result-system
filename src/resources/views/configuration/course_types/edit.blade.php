@extends('adminlte::page')

@section('title', 'Edit Course Type')

@section('content_header')
    <h3 class="mb-0">Edit Course Type</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('course-types.update', $courseType) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('configuration.course_types._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('course-types.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop