@php
    $f = fn ($value) => \App\Services\ResultService::fmt($value);
    $colors = \App\Services\SemesterResultService::COLORS;
@endphp

<div class="row mb-3">
    <div class="col-md-4"><strong>Student:</strong> {{ $student->full_name }}</div>
    <div class="col-md-3"><strong>Roll No.:</strong> {{ $student->rollno }}</div>
    <div class="col-md-5">
        <strong>{{ $exam->course?->shortname }}</strong> · {{ $exam->batch?->label }}
        · {{ \App\Support\Term::base($exam->term?->name) }} · {{ $exam->exam_label }}
    </div>
</div>

@if ($item && $header)
    <div class="row text-center mb-3">
        <div class="col-4">
            <div class="fs-2 fw-bold">{{ number_format((float) $item->sgpa, 2) }}</div>
            <div class="text-muted small">SGPA</div>
        </div>
        <div class="col-4">
            <span class="badge text-bg-{{ $colors[$item->status] ?? 'secondary' }} fs-5">{{ $item->result_label }}</span>
            <div class="text-muted small mt-1">Result</div>
        </div>
        <div class="col-4">
            <div class="fs-2 fw-bold">{{ $item->backlogs }}</div>
            <div class="text-muted small">Backlogs</div>
        </div>
    </div>

    <div class="alert alert-light border">
        <div>
            <strong>SGPA</strong> = Σ(credit × grade point) ÷ Σ credits
            = {{ $f($item->credit_points) }} ÷ {{ $f($item->credits) }} = <strong>{{ number_format((float) $item->sgpa, 2) }}</strong>
            @unless ($header->sgpa_with_failed)
                <span class="text-muted">(failed subjects are left out)</span>
            @endunless
        </div>
        <div class="mt-1">
            <strong>Why {{ $item->result_label }}:</strong> {{ $explainer->explain($item, $header) }}
            @if ($item->backlog_subjects)
                <span class="text-muted">Backlog: {{ $item->backlog_subjects }}.</span>
            @endif
        </div>
    </div>
@else
    <div class="alert alert-secondary">
        SGPA is not calculated yet. Click <strong>Calculate SGPA</strong> on the Result page after every subject is added.
    </div>
@endif

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject</th>
                <th class="text-center">Credit</th>
                <th class="text-center">Attempt</th>
                <th class="text-center">Grade</th>
                <th class="text-center">Grade Point</th>
                <th class="text-center">Credit × Point</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($lines as $line)
            @php
                $subject = $line['subject'];
                $row = $line['row'];
            @endphp
            <tr>
                <td>{{ $subject->subjectcode }}</td>
                <td>{{ $subject->subjectname }}</td>
                @if ($row)
                    <td class="text-center">{{ $f($row->subject_credit) }}</td>
                    <td class="text-center">{{ $row->attempt }}</td>
                    <td class="text-center"><strong>{{ $row->grade }}</strong></td>
                    <td class="text-center">{{ $row->grade_point }}</td>
                    <td class="text-center">{{ $row->is_pass ? $f($row->subject_credit * $row->grade_point) : 0 }}</td>
                    <td class="text-center">
                        @if ($row->is_absent)
                            <span class="badge text-bg-secondary">Absent</span>
                        @elseif ($row->is_pass)
                            <span class="badge text-bg-success">Passed</span>
                        @else
                            <span class="badge text-bg-danger">Backlog</span>
                        @endif
                    </td>
                @else
                    <td class="text-center">{{ $f($subject->subjectcredit) }}</td>
                    <td colspan="5" class="text-center"><span class="badge text-bg-warning">No result added</span></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>