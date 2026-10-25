@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h3 class="mb-0">Edit User</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-primary card-outline">
        <form action="{{ route('admin-users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('administration.users._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin-users.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop