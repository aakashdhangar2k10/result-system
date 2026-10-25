@extends('adminlte::page')

@section('title', 'Final Result Points')

@section('content_header')
    <h3 class="mb-0">Final Result Points</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info">
        The final result (total credits, CGPA, equivalent percentage and division) prints on the grade sheet of the semester
        where a final point ends. A course with no point set gets one final result after its last semester.
    </div>

    <div class="card card-primary card-outline">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">Sr. No</th>
                        <th>Course</th>
                        <th>Structure</th>
                        <th>Final result points</th>
                        <th style="width:90px">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($courses as $course)
                    @php
                        $word = $course->structure_type === 'year' ? 'Year' : 'Semester';
                        $rows = $finals->get($course->courseid, collect());
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $course->shortname }}</strong> <span class="text-muted">{{ $course->fullname }}</span></td>
                        <td>{{ $course->total_terms }} {{ $word }}{{ $course->total_terms == 1 ? '' : 's' }}</td>
                        <td>
                            @forelse ($rows as $row)
                                <span class="badge text-bg-primary">
                                    {{ $word }} {{ $row->from_term }}–{{ $row->to_term }}{{ $row->degree_label ? ' · ' . $row->degree_label : '' }}
                                </span>
                            @empty
                                <span class="text-muted">After the last {{ strtolower($word) }} ({{ $course->total_terms }}), automatic</span>
                            @endforelse
                        </td>
                        <td>
                            @can('coursefinals.edit')
                                <a href="{{ route('result-finals.edit', $course) }}" class="btn btn-sm btn-info text-white" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No courses yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop