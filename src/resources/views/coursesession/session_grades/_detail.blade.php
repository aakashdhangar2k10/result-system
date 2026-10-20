<p class="text-center text-muted mb-4">
    {{ $sessionGrade->course?->shortname }} – {{ $sessionGrade->batch?->label }}
    @if ($sessionGrade->batch?->branch)
        ({{ $sessionGrade->batch->branch->shortname }})
    @endif
</p>

<div class="mb-4">
    <h5 class="text-center text-info mb-2">Result rule</h5>
    <div class="text-center">
        @foreach ($sessionGrade->rule_lines as $line)
            <div class="mb-1">
                <span class="badge text-bg-{{ $line['color'] }} me-1">{{ $line['label'] }}</span> {{ $line['text'] }}
            </div>
        @endforeach
        <div class="text-muted small">
            The credits of failed subjects {{ $sessionGrade->sgpa_with_failed ? 'count in' : 'are left out of' }} the SGPA.
        </div>
    </div>
</div>

<h5 class="text-center text-info mb-3">
    Grade Name: {{ $sessionGrade->gradeType?->coursetypename ?? '—' }}
</h5>

<div class="table-responsive mb-4">
    <table class="table table-sm align-middle mb-0">
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>First Attempt Grade</th>
                <th>Credit</th>
                <th>Second Attempt Grade</th>
                <th>Credit</th>
                <th>Start Score</th>
                <th>End Score</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($grades as $grade)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $grade->firstattemptgrade }}</td>
                <td>{{ $grade->firstattemptcredit }}</td>
                <td>{{ $grade->secondattemptgrade }}</td>
                <td>{{ $grade->secondattemptcredit }}</td>
                <td>{{ $grade->startscore }}</td>
                <td>{{ $grade->endscore }}</td>
                <td>{{ $grade->description ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-3">No grades found for this grade type.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<h5 class="text-center text-info mb-3">
    Division Name: {{ $sessionGrade->divisionType?->divtypename ?? '—' }}
</h5>

<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Name</th>
                <th>Start Point</th>
                <th>End Point</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($divisions as $division)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $division->divisionname }}</td>
                <td>{{ (float) $division->startpoint }}</td>
                <td>{{ (float) $division->endpoint }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No divisions found for this division type.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>