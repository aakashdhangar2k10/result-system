@extends('adminlte::page')

@section('title', 'Batches')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Batch / Session</h3></div>
        <div class="col-sm-6 text-end">
            @can('batches.create')
                <a href="{{ route('batches.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Batch
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('batches.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select">
                            <option value="">All Courses</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}"
                                    @selected(request('courseid') == $course->courseid)>
                                    {{ $course->shortname }} – {{ $course->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('batches.index') }}" class="btn btn-light border">Reset</a>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span>Show</span>
                        <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span>Search:</span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto"
                               placeholder="Year, press Enter">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="course" label="Course" />
                        <x-sortable-th column="branch" label="Branch" />
                        <x-sortable-th column="startyear" label="Batch" />
                        <x-sortable-th column="endyear" label="End Year" />
                        <th>Description</th>
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($batches as $batch)
                    <tr>
                        <td>{{ $batches->firstItem() + $loop->index }}</td>
                        <td>{{ $batch->course?->shortname ?? '—' }}</td>
                        <td>{{ $batch->branch?->shortname ?? '—' }}</td>
                        <td><strong>{{ $batch->label }}</strong></td>
                        <td>{{ $batch->endyear }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($batch->description, 40) ?: '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $batch->active ? 'success' : 'secondary' }}">
                                {{ $batch->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('batches.edit')
                                <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('batches.delete')
                                <form action="{{ route('batches.destroy', $batch) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this batch?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No batches found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $batches->firstItem() ?? 0 }} to {{ $batches->lastItem() ?? 0 }} of {{ $batches->total() }} entries</span>
            {{ $batches->links() }}
        </div>
    </div>
@stop