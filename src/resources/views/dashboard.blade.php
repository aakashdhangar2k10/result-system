@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Dashboard</h3></div>
    </div>
@stop

@section('content')
    <x-adminlte-card title="Welcome, {{ auth()->user()->name }}" icon="bi bi-person" theme="primary" outline>
        Role: {{ auth()->user()->getRoleNames()->join(', ') }}
    </x-adminlte-card>
@stop
