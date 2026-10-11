@extends('adminlte::page')

@section('title', 'Add Division Type')

@section('content_header')
    <h3 class="mb-0">Add Division Type</h3>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('division-types.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('configuration.division_types._form')
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('division-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop