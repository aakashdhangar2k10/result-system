@extends('adminlte::page')

@section('title', 'Subject')

@section('content_header')
    <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Subject</h3></div>
        <div class="col-sm-6 text-end">
            @can('subjects.create')
                <a href="{{ route('subjects.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Subject
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
        <form method="GET" action="{{ route('subjects.index') }}">
            {{-- keep the current sorting when filtering/searching --}}
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="dir" value="{{ request('dir') }}">

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select">
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(request('courseid') == $course->courseid)>
                                    {{ $course->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semid" class="form-select">
                            <option value="">Select Semester</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->semid }}" @selected(request('semid') == $term->semid)>
                                    {{ \App\Support\Term::base($term->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Subject Type</label>
                        <select name="subjecttypeid" class="form-select">
                            <option value="">Select Subject Type</option>
                            @foreach ($subjectTypes as $subjectType)
                                <option value="{{ $subjectType->subjecttypeid }}"
                                    @selected(request('subjecttypeid') == $subjectType->subjecttypeid)>
                                    {{ $subjectType->subjecttypename }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-info text-white">Search</button>
                        <a href="{{ route('subjects.index') }}" class="btn btn-light border">Reset</a>
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
                               placeholder="Name or code, press Enter">
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
                        <x-sortable-th column="term" label="Semester / Year" />
                        <x-sortable-th column="subjecttype" label="Type" />
                        <x-sortable-th column="subjectname" label="Subject Name" />
                        <x-sortable-th column="subjectcode" label="Code" />
                        <x-sortable-th column="subjectcredit" label="Credit" />
                        <x-sortable-th column="maxinternalmarks" label="Max Internal" />
                        <x-sortable-th column="maxexternalmarks" label="Max External" />
                        <th>Total</th>
                        <x-sortable-th column="add_grade" label="Grade" />
                        <x-sortable-th column="active" label="Status" />
                        <th style="width:110px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($subjects as $subject)
                    <tr>
                        <td>{{ $subjects->firstItem() + $loop->index }}</td>
                        <td>{{ $subject->course?->shortname ?? '—' }}</td>
                        <td>{{ $subject->term_label }}</td>
                        <td>{{ $subject->type_label }}</td>
                        <td>{{ $subject->subjectname }}</td>
                        <td>{{ $subject->subjectcode }}</td>
                        <td>{{ (float) $subject->subjectcredit }}</td>
                        <td>{{ $subject->subjectType?->has_internal ? $subject->maxinternalmarks : '—' }}</td>
                        <td>{{ $subject->subjectType?->has_external ? $subject->maxexternalmarks : '—' }}</td>
                        <td>{{ $subject->total_marks ?: '—' }}</td>
                        <td>
                            @if ($subject->add_grade)
                                <span class="badge text-bg-primary">Yes</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $subject->active ? 'success' : 'secondary' }}">
                                {{ $subject->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('subjects.edit')
                                <a href="{{ route('subjects.edit', $subject) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                            @can('subjects.delete')
                                <form action="{{ route('subjects.destroy', $subject) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this subject?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center text-muted py-4">No subjects found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Showing {{ $subjects->firstItem() ?? 0 }} to {{ $subjects->lastItem() ?? 0 }} of {{ $subjects->total() }} entries</span>
            {{ $subjects->links() }}
        </div>
    </div>
@stop