@php
$semester = $list->semester;
$colors = ['regular' => 'primary', 'back' => 'warning', 'repeat' => 'info'];

$students = $list->students->sortBy(fn ($s) => [
$s->pivot->entry_type === 'regular' ? 0 : 1,
$s->batchid,
$s->rollno,
])->values();
@endphp

<div class="row mb-3">
    <div class="col-md-5"><strong>Course:</strong> {{ $semester->course?->fullname }}</div>
    <div class="col-md-3">
        <strong>Session:</strong> {{ $semester->batch?->label }}
        @if ($semester->batch?->branch) ({{ $semester->batch->branch->shortname }}) @endif
    </div>
    <div class="col-md-2"><strong>Semester:</strong> {{ \App\Support\Term::base($semester->term?->name) }}</div>
    <div class="col-md-2"><strong>Month:</strong> {{ $semester->semestermonth?->format('F-Y') }}</div>
</div>

@if ($list->is_locked)
<div class="alert alert-secondary py-2">
    <i class="bi bi-lock-fill"></i> Locked
    @if ($list->locked_at) on {{ $list->locked_at->format('d M Y, h:i A') }} @endif
    @if ($list->locker) by {{ $list->locker->name }} @endif.
    The list cannot be edited or deleted until it is unlocked.
</div>
@endifdif

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Roll No.</th>
                <th>Enrollment No.</th>
                <th>Student Name</th>
                <th>Session</th>
                <th>Entry</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
            @php $type = $student->pivot->entry_type; @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->rollno }}</td>
                <td>{{ $student->enrollmentno }}</td>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->batch?->label ?? '—' }}</td>
                <td>
                    <span class="badge text-bg-{{ $colors[$type] ?? 'secondary' }}">
                        {{ \App\Models\Student\StudentList::TYPES[$type] ?? $type }}
                    </span>
                </td>
                <td>{{ $student->pivot->remark ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-3">No students in this list.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($list->lockLog->isNotEmpty())
<h6 class="fw-bold mt-4">Lock history</h6>
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>By</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list->lockLog->take(10) as $entry)
            <tr>
                <td class="text-nowrap">{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                <td>
                    <span class="badge text-bg-{{ $entry->action === 'lock' ? 'secondary' : 'warning' }}">
                        {{ $entry->action === 'lock' ? 'Locked' : 'Unlocked' }}
                    </span>
                </td>
                <td>{{ $entry->user?->name ?? '—' }}</td>
                <td>{{ $entry->note ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif