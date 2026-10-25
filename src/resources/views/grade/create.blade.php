@extends('adminlte::page')

@section('title', 'Add Grade')

@section('content_header')
    <h3 class="mb-0">Add Grade</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('grades.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('grade._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="reset" class="btn btn-primary">Reset</button>
                <a href="{{ route('grades.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop