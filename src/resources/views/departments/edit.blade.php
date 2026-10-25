@extends('adminlte::page')

@section('title', 'Edit Department')

@section('content_header')
    <h3 class="mb-0">Edit Department</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('departments.update', $department) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('departments._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Update</button>
                 <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('departments.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop