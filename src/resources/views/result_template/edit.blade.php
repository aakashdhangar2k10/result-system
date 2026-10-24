@extends('adminlte::page')

@section('title', 'Edit Result Template')

@section('content_header')
    <h3 class="mb-0">Edit Result Template</h3>
@stop

@section('content')
    @include('partials.flash-error')

    <div class="card card-info card-outline">
        <form action="{{ route('result-templates.update', $template) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('result_template._form')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('result-templates.preview', $template) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i> Preview (saved settings)
                </a>
                <div>
                    <button type="submit" class="btn btn-info text-white">Update</button>
                    <a href="{{ route('result-templates.index') }}" class="btn btn-danger">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@stop