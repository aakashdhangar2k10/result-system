@extends('adminlte::page')

@section('title', 'Edit Group')

@section('content_header')
    <h3 class="mb-0">Edit Group</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-info card-outline">
        <form action="{{ route('admin-groups.update', $group) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('administration.groups._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Update</button>
                <a href="{{ route('admin-groups.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop