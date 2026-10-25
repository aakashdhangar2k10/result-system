@extends('adminlte::page')

@section('title', 'Edit Division')

@section('content_header')
    <h3 class="mb-0">Edit Division</h3>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <form action="{{ route('divisions.update', $division) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('division._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('divisions.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop