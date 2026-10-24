@extends('adminlte::page')

@section('title', 'Result Templates')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Result Templates</h3></div>
        <div class="col-sm-6 text-end">
            @can('coursefinals.view')
                <a href="{{ route('result-finals.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-flag"></i> Final Result Points
                </a>
            @endcan
            @can('resulttemplates.create')
                <a href="{{ route('result-templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash-error')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px">Sr. No</th>
                        <th>Template</th>
                        <th>Used for</th>
                        <th>Layout</th>
                        <th>Footer</th>
                        <th>Printed</th>
                        <th>Status</th>
                        <th style="width:190px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($templates as $template)
                    @php
                        $locked = $template->usages_count > 0 && ! auth()->user()->isAdmin();
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $template->name }}</strong>
                            @if ($template->is_default) <span class="badge text-bg-primary">Default</span> @endif
                            <div class="small text-muted">{{ $template->title ?: 'No title' }}</div>
                        </td>
                        <td class="small">{{ $template->assignment_text }}</td>
                        <td class="small">
                            <span class="badge text-bg-{{ $template->show_header ? 'success' : 'secondary' }}">Header {{ $template->show_header ? 'on' : 'off' }}</span>
                            <span class="badge text-bg-{{ $template->show_photo ? 'success' : 'secondary' }}">Photo {{ $template->show_photo ? 'on' : 'off' }}</span>
                            <span class="badge text-bg-{{ $template->show_final_result ? 'success' : 'secondary' }}">Final {{ $template->show_final_result ? 'on' : 'off' }}</span>
                            <div class="text-muted">Margins: {{ $template->margin_text }}</div>
                        </td>
                        <td class="small">
                            @if ($template->show_footer && $template->footer_items)
                                {{ implode(', ', $template->footer_items) }}
                            @else
                                <span class="text-muted">Off</span>
                            @endif
                        </td>
                        <td>
                            @if ($template->usages_count > 0)
                                <span class="badge text-bg-warning" title="Locked: only an administrator can edit it">
                                    <i class="bi bi-lock-fill"></i> Used {{ $template->usages_count }}×
                                </span>
                            @else
                                <span class="text-muted">Not used yet</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $template->active ? 'success' : 'secondary' }}">{{ $template->active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-nowrap">
                            @can('resulttemplates.view')
                                <a href="{{ route('result-templates.preview', $template) }}" target="_blank" class="btn btn-sm btn-success" title="Preview">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan

                            @can('resulttemplates.edit')
                                @if ($locked)
                                    <span class="btn btn-sm btn-secondary disabled" title="Used for printed sheets: only an administrator can edit">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                @else
                                    <a href="{{ route('result-templates.edit', $template) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endif
                            @endcan

                            @can('resulttemplates.create')
                                <form action="{{ route('result-templates.duplicate', $template) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-primary" title="Duplicate: make a copy you can edit"><i class="bi bi-files"></i></button>
                                </form>
                            @endcan

                            @can('resulttemplates.delete')
                                @if (! $template->is_default && $template->usages_count === 0)
                                    <form action="{{ route('result-templates.destroy', $template) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the template {{ e($template->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No templates yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop