@extends('adminlte::page')

@section('title', 'Edit Result Template')

@section('content_header')
    <h3 class="mb-0">Edit Result Template</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @if ($used > 0)
        <div class="alert alert-warning">
            <i class="bi bi-lock-fill"></i>
            <strong>This template was used to print {{ $used }} grade sheet(s)</strong>
            @if ($lastUsed) (last on {{ \Illuminate\Support\Carbon::parse($lastUsed)->format('d M Y, h:i A') }}) @endif.
            Only an administrator can change it. Sheets that are reprinted later will use the new layout,
            so for a different layout use <em>Duplicate</em> on the list instead.
        </div>
    @endif

    <div class="card card-info card-outline">
        <form action="{{ route('result-templates.update', $template) }}" method="POST" enctype="multipart/form-data"
              @if ($used > 0)
                  onsubmit="return confirm('This template was used for {{ $used }} printed sheet(s). Your changes also apply when those sheets are reprinted. Save anyway?')"
              @endif>
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