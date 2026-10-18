@extends('adminlte::page')

@section('title', 'Edit Subject')

@section('content_header')
    <h3 class="mb-0">Edit Subject</h3>
@stop

@section('content')
    <div class="card card-info card-outline">
        <form action="{{ route('subjects.update', $subject) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('subject._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('subjects.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop