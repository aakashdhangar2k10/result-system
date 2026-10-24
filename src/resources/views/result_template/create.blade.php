@extends('adminlte::page')

@section('title', 'Add Result Template')

@section('content_header')
    <h3 class="mb-0">Add Result Template</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-info card-outline">
        <form action="{{ route('result-templates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @include('result_template._form')
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-info text-white">Submit</button>
                <a href="{{ route('result-templates.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>
@stop