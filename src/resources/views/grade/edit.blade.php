@extends('adminlte::page')

@section('title', 'Edit Grade')

@section('content_header')
    <h3 class="mb-0">Edit Grade</h3>
@stop

@section('content')
    <div class="card card-info card-outline">
        <form action="{{ route('grades.update', $grade) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('grade._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Update</button>
                <button type="reset" class="btn btn-primary">Reset</button>
                <a href="{{ route('grades.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop