<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Semester Month</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" value="{{ $semester->semestermonth?->format('Y-m-d') }}" disabled>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Students</label>
    <div class="col-sm-9">

        {{-- students of the session: regular entries --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Students of session {{ $semester->batch?->label }}</strong>
                <input type="search" class="form-control form-control-sm w-auto" placeholder="Search" data-table="homeTable">
            </div>
            <div class="card-body p-0 table-responsive" style="max-height:420px">
                <table class="table table-sm table-striped align-middle mb-0" id="homeTable">
                    <thead class="sticky-top bg-body">
                        <tr>
                            <th style="width:70px">Sr. No</th>
                            <th>Roll No.</th>
                            <th>Enrollment No.</th>
                            <th>Student Name</th>
                            <th>Note</th>
                            <th style="width:90px">
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="checkAllHome">
                                    <label class="form-check-label" for="checkAllHome">All</label>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($home as $student)
                        <tr data-search="{{ mb_strtolower($student->rollno . ' ' . $student->enrollmentno . ' ' . $student->full_name) }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->rollno }}</td>
                            <td>{{ $student->enrollmentno }}</td>
                            <td>{{ $student->full_name }}</td>
                            <td>
                                @if ($previousIds !== null && ! in_array($student->studentid, $previousIds))
                                    <span class="badge text-bg-warning">Not in previous semester list</span>
                                @endif
                                @unless ($student->active)
                                    <span class="badge text-bg-secondary">Deactive</span>
                                @endunless
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input home-check" name="students[]"
                                       value="{{ $student->studentid }}"
                                       @checked(in_array($student->studentid, $selectedHome))>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">
                            No students in this session yet. Add them under Student first.
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                Showing {{ $home->count() }} students. Selected: <span id="selectedSummary"></span>
            </div>
        </div>

        {{-- students of other sessions of the same course: back paper / repeat entries --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <a class="fw-bold text-decoration-none" data-bs-toggle="collapse" href="#extraBox" role="button"
                   aria-expanded="{{ $selectedExtra ? 'true' : 'false' }}" aria-controls="extraBox">
                    <i class="bi bi-arrow-repeat"></i> Back / Repeat students from other sessions
                    ({{ count($selectedExtra) }} selected)
                </a>
                <input type="search" class="form-control form-control-sm w-auto" placeholder="Search" data-table="extraTable">
            </div>

            <div id="extraBox" class="collapse {{ $selectedExtra ? 'show' : '' }}">
                <div class="card-body border-bottom small text-muted">
                    Students of this course from <strong>other sessions</strong> who failed this semester
                    (<em>Back paper</em>), or are re-admitted for it (<em>Repeat semester</em>). Their own session does not change.
                </div>

                <div class="card-body p-0 table-responsive" style="max-height:360px">
                    <table class="table table-sm table-striped align-middle mb-0" id="extraTable">
                        <thead class="sticky-top bg-body">
                            <tr>
                                <th style="width:60px">Add</th>
                                <th>Roll No.</th>
                                <th>Enrollment No.</th>
                                <th>Student Name</th>
                                <th>Session</th>
                                <th style="width:170px">Type</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($extra as $student)
                            @php $picked = $selectedExtra[$student->studentid] ?? null; @endphp
                            <tr data-search="{{ mb_strtolower($student->rollno . ' ' . $student->enrollmentno . ' ' . $student->full_name . ' ' . $student->batch?->label) }}">
                                <td>
                                    <input type="checkbox" class="form-check-input extra-check" name="extra_ids[]"
                                           value="{{ $student->studentid }}" @checked($picked)>
                                </td>
                                <td>{{ $student->rollno }}</td>
                                <td>{{ $student->enrollmentno }}</td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->batch?->label ?? '—' }}</td>
                                <td>
                                    <select name="extra_type[{{ $student->studentid }}]" class="form-select form-select-sm">
                                        @foreach (\App\Models\Student\StudentList::EXTRA_TYPES as $value => $label)
                                            <option value="{{ $value }}" @selected(($picked['type'] ?? 'back') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="extra_remark[{{ $student->studentid }}]" maxlength="255"
                                           value="{{ $picked['remark'] ?? '' }}"
                                           class="form-control form-control-sm" placeholder="e.g. failed in 2 papers">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">
                                There are no students from other sessions of this course.
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Alert!</strong><br>
            * Please add all the students in the Student list before creating the result of the semester.
            Once the result is created, you cannot add or edit students in this list.
        </div>
    </div>
</div>