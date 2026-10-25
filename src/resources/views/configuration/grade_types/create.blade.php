@extends('adminlte::page')

@section('title', 'Add Grade Type')

@section('content_header')
    <h3 class="mb-0">Add Grade Type</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('grade-types.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('configuration.grade_types._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('grade-types.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop