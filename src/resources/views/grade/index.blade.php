@extends('adminlte::page')

@section('title', 'Grade')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Grade</h3></div>
        <div class="col-sm-6 text-end">
                @can('grades.create')
                    <a href="{{ route('grades.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Grade
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
        <form method="GET" action="{{ route('grades.index') }}">
            <div class="card-body">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Grade Type</label>
                        <select name="gradetypeid" class="form-select">
                            <option value="">Select Grade Type</option>
                            @foreach ($gradeTypes as $gradeType)
                                <option value="{{ $gradeType->coursetypeid }}"
                                    @selected(request('gradetypeid') == $gradeType->coursetypeid)>
                                    {{ $gradeType->coursetypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('grades.index') }}" class="btn btn-light border">Reset</a>
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
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto">
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <th>Course</th>
                        <th>First Attempt Grade</th>
                        <th>Credit</th>
                        <th>Second Attempt Grade</th>
                        <th>Credit</th>
                        <th>Start Score</th>
                        <th>End Score</th>
                        <th>Description</th>
                        <th>Grade Type</th>
                        <th>Status</th>
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($grades as $grade)
                    <tr>
                        <td>{{ $grades->firstItem() + $loop->index }}</td>
                        <td>{{ $grade->courseType?->fullname ?? '—' }}</td>
                        <td>{{ $grade->firstattemptgrade }}</td>
                        <td>{{ $grade->firstattemptcredit }}</td>
                        <td>{{ $grade->secondattemptgrade }}</td>
                        <td>{{ $grade->secondattemptcredit }}</td>
                        <td>{{ $grade->startscore }}</td>
                        <td>{{ $grade->endscore }}</td>
                        <td>{{ $grade->description ?? '—' }}</td>
                        <td>{{ $grade->gradeType?->coursetypename ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $grade->active ? 'success' : 'secondary' }}">
                                {{ $grade->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('grades.edit')
                                <a href="{{ route('grades.edit', $grade) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('grades.delete')
                                <form action="{{ route('grades.destroy', $grade) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this grade?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No grades found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $grades->firstItem() ?? 0 }} to {{ $grades->lastItem() ?? 0 }} of {{ $grades->total() }} entries</span>
            {{ $grades->links() }}
        </div>
    </div>
@stop