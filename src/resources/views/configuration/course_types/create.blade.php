@extends('adminlte::page')

@section('title', 'Add Course Type')

@section('content_header')
    <h3 class="mb-0">Add Course Type</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('course-types.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('configuration.course_types._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('course-types.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop