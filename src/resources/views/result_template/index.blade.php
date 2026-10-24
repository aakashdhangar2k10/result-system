@extends('adminlte::page')

@section('title', 'Result Templates')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Result Templates</h3></div>
        <div class="col-sm-6 text-end">
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
                        <th style="width:70px">Sr. No</th>
                        <th>Template</th>
                        <th>Used for</th>
                        <th>Title</th>
                        <th>Header</th>
                        <th>Photo</th>
                        <th>Footer</th>
                        <th>Margins</th>
                        <th>Status</th>
                        <th style="width:150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $template->name }}</strong>
                            @if ($template->is_default) <span class="badge text-bg-primary">Default</span> @endif
                        </td>
                        <td>{{ $template->course ? $template->course->shortname : 'All courses' }}</td>
                        <td>{{ $template->title }}</td>
                        <td>
                            <span class="badge text-bg-{{ $template->show_header ? 'success' : 'secondary' }}">{{ $template->show_header ? 'On' : 'Off' }}</span>
                            @if ($template->show_header && $template->show_department) <span class="badge text-bg-light border">Department</span> @endif
                        </td>
                        <td><span class="badge text-bg-{{ $template->show_photo ? 'success' : 'secondary' }}">{{ $template->show_photo ? 'On' : 'Off' }}</span></td>
                        <td class="small">
                            @if ($template->show_footer && $template->footer_items)
                                {{ implode(', ', $template->footer_items) }}
                            @else
                                <span class="text-muted">Off</span>
                            @endif
                        </td>
                        <td class="small">{{ $template->margin_text }}</td>
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
                                <a href="{{ route('result-templates.edit', $template) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('resulttemplates.delete')
                                @unless ($template->is_default)
                                    <form action="{{ route('result-templates.destroy', $template) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete the template {{ e($template->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No templates yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop