@extends('adminlte::page')

@section('title', 'Add Semester')

@section('content_header')
    <h3 class="mb-0">Add Semester</h3>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('semesters.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('configuration.semesters._form')
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop