@extends('adminlte::page')

@section('title', 'Edit Grade Type')

@section('content_header')
    <h3 class="mb-0">Edit Grade Type</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('grade-types.update', $gradeType) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('configuration.grade_types._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('grade-types.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop