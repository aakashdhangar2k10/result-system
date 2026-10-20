@php
    $f = fn ($value) => \App\Services\ResultService::fmt($value);
    $marks = $result->entry_mode === 'marks';
    $hasInternal = $result->max_internal > 0;
    $hasExternal = $result->max_external > 0;
    $show = fn ($value, $absent) => $absent ? 'Ab' : $f($value);
@endphp

<div class="row mb-3">
    <div class="col-md-4"><strong>Course:</strong> {{ $result->course?->fullname }}</div>
    <div class="col-md-3">
        <strong>Session:</strong> {{ $result->batch?->label }}
        @if ($result->batch?->branch) ({{ $result->batch->branch->shortname }}) @endif
    </div>
    <div class="col-md-2"><strong>Semester:</strong> {{ \App\Support\Term::base($result->term?->name) }}</div>
    <div class="col-md-3"><strong>Attempt:</strong> {{ $result->exam?->exam_label }}</div>
</div>
<div class="row mb-3">
    <div class="col-md-5"><strong>Subject:</strong> {{ $result->subject?->subjectname }} ({{ $result->subject?->subjectcode }})</div>
    <div class="col-md-3"><strong>Credit:</strong> {{ $f($result->subject_credit) }}</div>
    <div class="col-md-4">
        <strong>Entry:</strong> {{ \App\Models\Result\SubjectResult::MODES[$result->entry_mode] ?? $result->entry_mode }}
        @if ($marks) (Internal {{ $result->max_internal }} + External {{ $result->max_external }}) @endif
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Roll No.</th>
                <th>Student Name</th>
                @if ($marks)
                    @if ($hasInternal)
                        <th class="text-center">T1</th><th class="text-center">T2</th><th class="text-center">T3</th>
                        <th class="text-center">Internal</th>
                    @endif
                    @if ($hasExternal)<th class="text-center">External</th>@endif
                    <th class="text-center">Total</th>
                    <th class="text-center">%</th>
                @endif
                <th class="text-center">Grade</th>
                <th class="text-center">Grade Point</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            @php $tone = $item->is_absent ? 'secondary' : ($item->is_pass ? 'success' : 'danger'); @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->student?->rollno }}</td>
                <td>{{ $item->student?->full_name }}</td>
                @if ($marks)
                    @if ($hasInternal)
                        <td class="text-center">{{ $show($item->t1, $item->t1_absent) }}</td>
                        <td class="text-center">{{ $show($item->t2, $item->t2_absent) }}</td>
                        <td class="text-center">{{ $show($item->t3, $item->t3_absent) }}</td>
                        <td class="text-center fw-bold">{{ $f($item->internal) }}</td>
                    @endif
                    @if ($hasExternal)
                        <td class="text-center">{{ $show($item->external, $item->external_absent) }}</td>
                    @endif
                    <td class="text-center fw-bold">{{ $f($item->total) }}</td>
                    <td class="text-center">{{ $f($item->percentage) }}</td>
                @endif
                <td class="text-center"><span class="badge text-bg-{{ $tone }}">{{ $item->grade }}</span></td>
                <td class="text-center">{{ $item->grade_point }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>