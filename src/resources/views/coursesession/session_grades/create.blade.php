@extends('adminlte::page')

@section('title', 'Add Session Grade')

@section('content_header')
    <h3 class="mb-0">Add Session Grade</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('session-grades.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('coursesession.session_grades._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('session-grades.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop