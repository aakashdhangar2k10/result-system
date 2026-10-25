@extends('adminlte::page')

@section('title', 'Add Semester')

@section('content_header')
    <h3 class="mb-0">Add Semester</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('semesters.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('configuration.semesters._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop