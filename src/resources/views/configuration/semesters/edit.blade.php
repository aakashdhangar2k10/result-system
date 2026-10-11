@extends('adminlte::page')

@section('title', 'Edit Semester')

@section('content_header')
    <h3 class="mb-0">Edit Semester</h3>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('semesters.update', $semester) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('configuration.semesters._form')
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('semesters.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop