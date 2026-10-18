@extends('adminlte::page')

@section('title', 'Add Batch')

@section('content_header')
    <h3 class="mb-0">Add Batch</h3>
@stop

@section('content')
    <div class="card card-info card-outline">
        <form action="{{ route('batches.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('batch._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Submit</button>
                <button type="reset" class="btn btn-light border">Reset</button>
                <a href="{{ route('batches.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop