@extends('adminlte::page')

@section('title', 'Add User')

@section('content_header')
    <h3 class="mb-0">Add User</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-primary card-outline">
        <form action="{{ route('admin-users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('administration.users._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('admin-users.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop