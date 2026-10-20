@extends('adminlte::page')

@section('title', 'Division')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Division</h3></div>
        <div class="col-sm-6 text-end">
            @can('divisions.create')
                <a href="{{ route('divisions.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Division
                </a>
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
        <form method="GET" action="{{ route('divisions.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Course Type</label>
                        <select name="coursetypeid" class="form-select">
                            <option value="">Select Course Type</option>
                            @foreach ($courseTypes as $courseType)
                                <option value="{{ $courseType->coursetypeid }}"
                                    @selected(request('coursetypeid') == $courseType->coursetypeid)>
                                    {{ $courseType->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Division Type</label>
                        <select name="divtypeid" class="form-select">
                            <option value="">Select Division Type</option>
                            @foreach ($divisionTypes as $divisionType)
                                <option value="{{ $divisionType->divtypeid }}"
                                    @selected(request('divtypeid') == $divisionType->divtypeid)>
                                    {{ $divisionType->divtypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('divisions.index') }}" class="btn btn-light border">Reset</a>
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
                               placeholder="Press Enter">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <x-sortable-th column="course" label="Course Type" />
                        <x-sortable-th column="divisiontype" label="Division Type" />
                        <x-sortable-th column="divisionname" label="Division Name" />
                        <x-sortable-th column="startpoint" label="Start Point" />
                        <x-sortable-th column="endpoint" label="End Point" />
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($divisions as $division)
                    <tr>
                        <td>{{ $divisions->firstItem() + $loop->index }}</td>
                        <td>{{ $division->courseType?->fullname ?? '—' }}</td>
                        <td>{{ $division->divisionType?->divtypename ?? '—' }}</td>
                        <td>{{ $division->divisionname }}</td>
                        <td>{{ $division->startpoint }}</td>
                        <td>{{ $division->endpoint }}</td>
                        <td>
                            <span class="badge text-bg-{{ $division->active ? 'success' : 'secondary' }}">
                                {{ $division->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('divisions.edit')
                                <a href="{{ route('divisions.edit', $division) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('divisions.delete')
                                <form action="{{ route('divisions.destroy', $division) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this division?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No divisions found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $divisions->firstItem() ?? 0 }} to {{ $divisions->lastItem() ?? 0 }} of {{ $divisions->total() }} entries</span>
            {{ $divisions->links() }}
        </div>
    </div>
@stop