@extends('adminlte::page')

@section('title', 'Add Department')

@section('content_header')
    <h3 class="mb-0">Add Department</h3>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('departments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('departments._form')
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop