@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <h3 class="mb-0">Edit Role</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-info card-outline">
        <form action="{{ route('admin-roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('administration.roles._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Update</button>
                <a href="{{ route('admin-roles.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop