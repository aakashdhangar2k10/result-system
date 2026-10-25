<div class="row">
    <div class="col-md-3 text-center mb-3">
        @if ($student->image_url)
            <img src="{{ $student->image_url }}" alt="{{ $student->full_name }}" class="img-thumbnail" style="max-height:190px">
        @else
            <div class="border rounded p-4 text-muted">No photo</div>
        @endif
    </div>

    <div class="col-md-9">
        <table class="table table-sm mb-0">
            <tr><th style="width:170px">Student Name</th><td>{{ $student->full_name }}</td></tr>
            <tr><th>Roll No.</th><td>{{ $student->rollno }}</td></tr>
            <tr><th>Enrollment No.</th><td>{{ $student->enrollmentno }}</td></tr>
            <tr><th>APAAR ID</th><td>{{ $student->apaarid ?: '—' }}</td></tr>
            <tr><th>Course</th><td>{{ $student->course?->fullname ?? '—' }}</td></tr>
            <tr>
                <th>Session</th>
                <td>
                    {{ $student->batch?->label ?? '—' }}
                    @if ($student->batch?->branch) ({{ $student->batch->branch->shortname }}) @endif
                </td>
            </tr>
            <tr><th>{{ $student->father_label ?: 'Father' }}'s Name</th><td>{{ $student->father_display }}</td></tr>
            <tr><th>Mother's Name</th><td>{{ $student->mother_display }}</td></tr>
            <tr><th>Subject</th><td>{{ $student->subject ?: '—' }}</td></tr>
            <tr><th>Address</th><td>{{ $student->address ?: '—' }}</td></tr>
            <tr><th>Mobile</th><td>{{ $student->mobile ?: '—' }}</td></tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="badge text-bg-{{ $student->active ? 'success' : 'secondary' }}">
                        {{ $student->active ? 'Active' : 'Deactive' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>