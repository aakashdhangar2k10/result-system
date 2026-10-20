@if (! empty($message))
    <div class="alert alert-warning mb-0">{{ $message }}</div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="checkAllStudents">
            <label class="form-check-label fw-bold" for="checkAllStudents">All</label>
        </div>
        <input type="search" id="studentSearch" class="form-control form-control-sm w-auto" placeholder="Search name or roll no.">
    </div>

    <div style="max-height:420px; overflow:auto">
        @forelse ($students as $student)
            @php $entry = $student->pivot->entry_type ?? 'regular'; @endphp
            <div class="form-check student-row" data-search="{{ mb_strtolower($student->full_name . ' ' . $student->rollno) }}">
                <input class="form-check-input student-check" type="checkbox" name="students[]"
                       id="student_{{ $student->studentid }}" value="{{ $student->studentid }}"
                       @checked(in_array($student->studentid, $selected))>
                <label class="form-check-label" for="student_{{ $student->studentid }}">
                    {{ $student->full_name }} (<strong>{{ $student->rollno }}</strong>)
                    @if ($entry !== 'regular')
                        <span class="badge text-bg-warning">{{ \App\Models\Student\StudentList::TYPES[$entry] ?? $entry }}</span>
                    @endif
                    @unless ($student->active)
                        <span class="badge text-bg-secondary">Deactive</span>
                    @endunless
                </label>
            </div>
        @empty
            <div class="text-muted">The student list of this semester has no students.</div>
        @endforelse

        @foreach ($stale as $student)
            <div class="form-check student-row" data-search="{{ mb_strtolower($student->full_name . ' ' . $student->rollno) }}">
                <input class="form-check-input student-check" type="checkbox" name="students[]"
                       id="student_{{ $student->studentid }}" value="{{ $student->studentid }}"
                       @checked(in_array($student->studentid, $selected))>
                <label class="form-check-label" for="student_{{ $student->studentid }}">
                    {{ $student->full_name }} (<strong>{{ $student->rollno }}</strong>)
                    <span class="badge text-bg-danger">No longer on the student list: untick to remove</span>
                </label>
            </div>
        @endforeach
    </div>

    <div class="small text-muted mt-2">Selected: <span id="studentSummary"></span></div>
@endif