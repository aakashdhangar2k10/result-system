@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h3 class="mb-0">Dashboard</h3>
@stop

@section('content')
    @include('partials.flash-error')

    {{-- who is logged in --}}
    <div class="card card-primary card-outline">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">Welcome, {{ $user->name }}</h5>
                <div class="small">
                    @foreach ($user->roles as $role)
                        <span class="badge text-bg-primary">{{ $role->name }}</span>
                    @endforeach
                    @foreach ($user->groups as $group)
                        <span class="badge text-bg-info">{{ $group->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="small text-muted">
                <i class="bi bi-building"></i>
                @if ($user->isAdmin() || $user->all_departments)
                    All departments
                @else
                    {{ $user->departments->pluck('fullname')->implode(', ') ?: 'No department assigned' }}
                @endif
            </div>
        </div>
    </div>

    @if (! $full)
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Use the menu on the left to open the pages you have access to.
        </div>
    @else
        {{-- counts --}}
        @if ($tiles->isNotEmpty())
            <div class="row g-3 mb-3">
                @foreach ($tiles as $tile)
                    <div class="col-6 col-md-4 col-xl">
                        <a href="{{ $tile['url'] }}" class="text-decoration-none text-reset">
                            <div class="card h-100 mb-0 border-start border-4 border-{{ $tile['color'] }}">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fs-2 fw-bold lh-1">{{ number_format($tile['value']) }}</div>
                                        <div class="text-muted">{{ $tile['label'] }}</div>
                                    </div>
                                    <i class="bi {{ $tile['icon'] }} fs-1 text-{{ $tile['color'] }} opacity-75"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- what needs doing --}}
        @canany(['subjectresults.view', 'studentimports.view'])
            @php
                $needsSgpa = $attention['needsSgpa'];
                $stale = $attention['stale'];
                $previews = $attention['previews'];
                $link = fn ($exam) => route('results.index', [
                    'courseid' => $exam->courseid, 'batchid' => $exam->batchid,
                    'semid' => $exam->semid, 'semesterexamid' => $exam->semesterexamid,
                ]);
            @endphp

            @if ($needsSgpa->isNotEmpty() || $stale->isNotEmpty() || $previews > 0)
                <div class="card border-warning">
                    <div class="card-header bg-warning-subtle">
                        <h3 class="card-title mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Needs attention</h3>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($needsSgpa as $exam)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="badge text-bg-warning">SGPA not calculated</span>
                                    <strong>{{ $exam->course?->shortname }}</strong> · {{ $exam->batch?->label }}
                                    · {{ \App\Support\Term::base($exam->term?->name) }} · {{ $exam->exam_label }}
                                </span>
                                <a href="{{ $link($exam) }}" class="btn btn-sm btn-primary">Open</a>
                            </li>
                        @endforeach

                        @foreach ($stale as $row)
                            @php $exam = $row->exam; @endphp
                            @if ($exam)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="badge text-bg-danger">SGPA out of date</span>
                                        <strong>{{ $exam->course?->shortname }}</strong> · {{ $exam->batch?->label }}
                                        · {{ \App\Support\Term::base($exam->term?->name) }} · {{ $exam->exam_label }}
                                    </span>
                                    <a href="{{ $link($exam) }}" class="btn btn-sm btn-primary">Recalculate</a>
                                </li>
                            @endif
                        @endforeach

                        @if ($previews > 0)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="badge text-bg-info">Student import</span>
                                    {{ $previews }} {{ \Illuminate\Support\Str::plural('sheet', $previews) }} waiting for your confirmation
                                </span>
                                <a href="{{ route('student-imports.index', ['status' => 'preview']) }}" class="btn btn-sm btn-primary">Review</a>
                            </li>
                        @endif
                    </ul>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Nothing needs your attention right now.
                </div>
            @endif
        @endcanany

        <div class="row g-3">
            {{-- latest results --}}
            @can('subjectresults.view')
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Latest result sheets</h3>
                            <a href="{{ route('results.index') }}" class="btn btn-sm btn-outline-primary">All results</a>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr><th>Exam</th><th style="width:38%">Result</th><th class="text-end">Calculated</th></tr>
                                </thead>
                                <tbody>
                                @forelse ($latest as $row)
                                    @php
                                        $exam = $row->exam;
                                        $total = max(1, $row->student_count);
                                    @endphp
                                    @continue(! $exam)
                                    <tr>
                                        <td>
                                            <a href="{{ route('results.index', ['courseid' => $exam->courseid, 'batchid' => $exam->batchid, 'semid' => $exam->semid, 'semesterexamid' => $exam->semesterexamid]) }}">
                                                <strong>{{ $exam->course?->shortname }}</strong>
                                            </a>
                                            <div class="small text-muted">
                                                {{ $exam->batch?->label }} · {{ \App\Support\Term::base($exam->term?->name) }} · {{ $exam->exam_label }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress" style="height:12px">
                                                <div class="progress-bar bg-success" style="width: {{ round($row->pass_count / $total * 100, 1) }}%"
                                                     title="{{ $row->pass_label }} {{ $row->pass_count }}"></div>
                                                <div class="progress-bar bg-primary" style="width: {{ round($row->promoted_count / $total * 100, 1) }}%"
                                                     title="{{ $row->promoted_label }} {{ $row->promoted_count }}"></div>
                                                <div class="progress-bar bg-danger" style="width: {{ round($row->fail_count / $total * 100, 1) }}%"
                                                     title="{{ $row->fail_label }} {{ $row->fail_count }}"></div>
                                            </div>
                                            <div class="small text-muted">
                                                {{ $row->student_count }} students ·
                                                {{ $row->pass_label }} {{ $row->pass_count }} ·
                                                {{ $row->promoted_label }} {{ $row->promoted_count }} ·
                                                {{ $row->fail_label }} {{ $row->fail_count }}
                                            </div>
                                        </td>
                                        <td class="text-end small text-nowrap">
                                            {{ $row->calculated_at?->format('d M Y') }}
                                            @if ($row->is_stale)
                                                <div><span class="badge text-bg-danger">out of date</span></div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No result has been calculated yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- students by course --}}
            @can('students.view')
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Active students by course</h3>
                            <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary">Students</a>
                        </div>
                        <div class="card-body">
                            @php $max = max(1, (int) $byCourse->max('total')); @endphp
                            @forelse ($byCourse as $row)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small">
                                        <span>{{ $row->course?->fullname ?? '—' }}</span>
                                        <strong>{{ $row->total }}</strong>
                                    </div>
                                    <div class="progress" style="height:8px">
                                        <div class="progress-bar" style="width: {{ round($row->total / $max * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">No students yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        {{-- quick actions --}}
        @if ($actions->isNotEmpty())
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title mb-0">Quick actions</h3></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @foreach ($actions as $action)
                        <a href="{{ $action['url'] }}" class="btn btn-outline-primary">
                            <i class="bi {{ $action['icon'] }}"></i> {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
@stop