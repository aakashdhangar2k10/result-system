@extends('adminlte::page')

@section('title', 'Add Group')

@section('content_header')
    <h3 class="mb-0">Add Group</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-primary card-outline">
        <form action="{{ route('admin-groups.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('administration.groups._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('admin-groups.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop