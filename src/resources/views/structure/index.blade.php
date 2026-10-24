@extends('adminlte::page')

@section('title', 'Structure & Flow')

@section('content_header')
    <h3 class="mb-0">Structure &amp; Flow</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @php
        $color = ['ok' => 'success', 'part' => 'warning', 'miss' => 'danger', 'stale' => 'warning', 'na' => 'secondary'];
        $icon = [
            'ok' => 'bi-check-circle-fill', 'part' => 'bi-dash-circle-fill', 'miss' => 'bi-x-circle-fill',
            'stale' => 'bi-exclamation-triangle-fill', 'na' => 'bi-circle',
        ];
        $user = auth()->user();
        $chain = ['Department', 'Course', 'Session', 'Result rule', 'Semester', 'Subject', 'Session subject', 'Student', 'Student list', 'Exam', 'Result', 'SGPA'];
    @endphp

    {{-- the flow --}}
    <div class="card">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-1 small">
            @foreach ($chain as $name)
                <span class="badge text-bg-light border">{{ $name }}</span>
                @unless ($loop->last) <i class="bi bi-arrow-right text-muted"></i> @endunless
            @endforeach
        </div>
        <div class="card-footer py-2 small d-flex flex-wrap gap-3">
            <span><span class="badge text-bg-success"><i class="bi bi-check-circle-fill"></i></span> complete</span>
            <span><span class="badge text-bg-warning"><i class="bi bi-dash-circle-fill"></i></span> partly done</span>
            <span><span class="badge text-bg-danger"><i class="bi bi-x-circle-fill"></i></span> missing</span>
            <span><span class="badge text-bg-warning"><i class="bi bi-exclamation-triangle-fill"></i></span> SGPA out of date</span>
            <span><span class="badge text-bg-secondary"><i class="bi bi-circle"></i></span> not applicable yet</span>
            <span class="text-muted">The numbers are done / total. Click a badge to open the screen where you fix it.</span>
        </div>
    </div>

    {{-- departments: overview --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title mb-0">Departments</h3></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-center">Courses</th>
                        <th class="text-center">Sessions</th>
                        <th class="text-center">Subjects</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">SGPA calculated</th>
                        <th style="width:22%">Setup complete</th>
                        @if ($canSwitch)<th style="width:150px"></th>@endif
                    </tr>
                </thead>
                <tbody>
                @forelse ($departments as $d)
                    @php $t = $d['totals']; @endphp
                    <tr>
                        <td>
                            <a href="#dept-{{ $d['id'] }}" class="fw-bold text-decoration-none">{{ $d['fullname'] }}</a>
                            <span class="text-muted">({{ $d['shortname'] }})</span>
                            @unless ($d['active']) <span class="badge text-bg-secondary">inactive</span> @endunless
                        </td>
                        <td class="text-center">{{ $t['courses'] }}</td>
                        <td class="text-center">{{ $t['sessions'] }}</td>
                        <td class="text-center">{{ $t['subjects'] }}</td>
                        <td class="text-center">{{ $t['students'] }}</td>
                        <td class="text-center">{{ $t['calculated'] }}</td>
                        <td>
                            <div class="progress" style="height:12px" title="{{ $t['ok'] }} of {{ $t['all'] }} steps complete">
                                <div class="progress-bar bg-{{ $t['percent'] >= 80 ? 'success' : ($t['percent'] >= 40 ? 'warning' : 'danger') }}"
                                     style="width: {{ $t['percent'] }}%"></div>
                            </div>
                            <div class="small text-muted">{{ $t['percent'] }}% · {{ $t['ok'] }} of {{ $t['all'] }} steps</div>
                        </td>
                        @if ($canSwitch)
                            <td class="text-end">
                                @if ((int) $selectedDepartment === $d['id'])
                                    <span class="badge text-bg-primary">Working here</span>
                                @else
                                    <form method="POST" action="{{ route('department-context.update') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="deptid" value="{{ $d['id'] }}">
                                        <button class="btn btn-sm btn-outline-primary">Work in this department</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No department found. Add one under Departments.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- department by department --}}
    @foreach ($departments as $d)
        <div class="card card-outline card-primary" id="dept-{{ $d['id'] }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="bi bi-building"></i> {{ $d['fullname'] }}</h3>
                <span class="small text-muted">{{ $d['totals']['courses'] }} courses · {{ $d['totals']['sessions'] }} sessions</span>
            </div>

            <div class="card-body p-0">
                @forelse ($d['courses'] as $course)
                    <div class="border-bottom px-3 py-2 bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-mortarboard-fill text-success"></i>
                            <strong>{{ $course['shortname'] }}</strong>
                            <span class="text-muted">{{ $course['fullname'] }}</span>
                            <span class="badge text-bg-light border">{{ $course['structure'] }}</span>
                            @if ($course['has_branch'])
                                <span class="badge text-bg-info">has branches</span>
                            @endif
                        </div>
                        <div class="small text-muted">{{ count($course['sessions']) }} {{ \Illuminate\Support\Str::plural('session', count($course['sessions'])) }}</div>
                    </div>

                    @if ($course['sessions'])
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:150px">Session</th>
                                        @foreach ($steps as $key => $meta)
                                            <th class="text-center small">{{ $meta[0] }}</th>
                                        @endforeach
                                        <th>Next step</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($course['sessions'] as $session)
                                    <tr>
                                        <td>
                                            <i class="bi bi-calendar-range text-warning"></i>
                                            <strong>{{ $session['label'] }}</strong>
                                            @if ($session['branch']) <span class="badge text-bg-info">{{ $session['branch'] }}</span> @endif
                                        </td>
                                        @foreach ($session['steps'] as $step)
                                            <td class="text-center">
                                                @if ($user->can($step['perm']))
                                                    <a href="{{ $step['url'] }}" class="badge text-bg-{{ $color[$step['status']] }} text-decoration-none"
                                                       title="{{ $step['label'] }}: {{ $step['text'] }}">
                                                        <i class="bi {{ $icon[$step['status']] }}"></i> {{ $step['text'] }}
                                                    </a>
                                                @else
                                                    <span class="badge text-bg-{{ $color[$step['status']] }}" title="{{ $step['label'] }}: {{ $step['text'] }}">
                                                        <i class="bi {{ $icon[$step['status']] }}"></i> {{ $step['text'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="small">
                                            @if ($session['next'])
                                                @if ($user->can($session['next']['perm']))
                                                    <a href="{{ $session['next']['url'] }}">{{ $session['next']['label'] }}</a>
                                                @else
                                                    {{ $session['next']['label'] }}
                                                @endif
                                            @else
                                                <span class="text-success"><i class="bi bi-check2-all"></i> All done</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($session['semesters'])
                                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#sem-{{ $session['id'] }}-{{ $course['id'] }}" title="Show the semesters">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($session['semesters'])
                                        <tr class="collapse" id="sem-{{ $session['id'] }}-{{ $course['id'] }}">
                                            <td colspan="{{ count($steps) + 3 }}" class="bg-body-tertiary">
                                                <table class="table table-sm table-bordered align-middle mb-0 bg-body">
                                                    <thead>
                                                        <tr>
                                                            <th>Semester</th>
                                                            <th>Month</th>
                                                            <th class="text-center">Subjects</th>
                                                            <th class="text-center">Session subjects</th>
                                                            <th class="text-center">Student list</th>
                                                            <th>Exams, results and SGPA</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($session['semesters'] as $sem)
                                                        <tr>
                                                            <td><strong>{{ $sem['label'] }}</strong></td>
                                                            <td>{{ $sem['month'] }}</td>
                                                            <td class="text-center">
                                                                <span class="badge text-bg-{{ $sem['subjects'] > 0 ? 'success' : 'danger' }}">{{ $sem['subjects'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge text-bg-{{ $sem['session_subject'] ? 'success' : 'danger' }}">
                                                                    <i class="bi {{ $sem['session_subject'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($sem['list'])
                                                                    <span class="badge text-bg-{{ $sem['list']['members'] > 0 ? 'success' : 'warning' }}">
                                                                        {{ $sem['list']['members'] }} students
                                                                    </span>
                                                                    @if ($sem['list']['locked']) <i class="bi bi-lock-fill text-secondary" title="Locked"></i> @endif
                                                                @else
                                                                    <span class="badge text-bg-danger"><i class="bi bi-x-circle-fill"></i></span>
                                                                @endif
                                                            </td>
                                                            <td class="small">
                                                                @forelse ($sem['exams'] as $exam)
                                                                    <div>
                                                                        @if ($user->can('subjectresults.view'))
                                                                            <a href="{{ route('results.index', ['courseid' => $course['id'], 'batchid' => $session['id'], 'semid' => $sem['semid'], 'semesterexamid' => $exam['id']]) }}">{{ $exam['label'] }}</a>
                                                                        @else
                                                                            {{ $exam['label'] }}
                                                                        @endif
                                                                        · {{ $exam['entered'] }}/{{ $sem['subjects'] }} subjects
                                                                        @if ($exam['stale'])
                                                                            <span class="badge text-bg-warning">SGPA out of date</span>
                                                                        @elseif ($exam['calculated'])
                                                                            <span class="badge text-bg-success">SGPA calculated</span>
                                                                        @elseif ($exam['entered'] > 0)
                                                                            <span class="badge text-bg-danger">SGPA not calculated</span>
                                                                        @endif
                                                                    </div>
                                                                @empty
                                                                    <span class="text-muted">No exam yet</span>
                                                                @endforelse
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-3 py-2 small text-muted">
                            No session yet.
                            @can('batches.create')
                                <a href="{{ route('batches.create') }}">Add a session</a>
                            @endcan
                        </div>
                    @endif
                @empty
                    <div class="px-3 py-3 text-muted">
                        This department has no course yet.
                        @can('courses.create')
                            <a href="{{ route('courses.create') }}">Add a course</a>
                        @endcan
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
@stop