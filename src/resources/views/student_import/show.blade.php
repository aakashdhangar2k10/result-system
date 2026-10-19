@extends('adminlte::page')

@section('title', 'Student Import')

@section('content_header')
    <div class="row">
        <div class="col-sm-9">
            <h3 class="mb-0">
                {{ $import->status === 'preview' ? 'Preview' : 'Import' }}: {{ $import->file_name }}
                <span class="badge text-bg-{{ $import->status_color }} fs-6 align-middle">{{ $import->status_label }}</span>
            </h3>
        </div>
        <div class="col-sm-3 text-end">
            @can('studentimports.view')
                <a href="{{ route('student-imports.index') }}" class="btn btn-outline-secondary">Import Log</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Course:</strong> {{ $import->course?->fullname ?? '—' }}</div>
                <div class="col-md-3">
                    <strong>Session:</strong> {{ $import->batch?->label ?? '—' }}
                    @if ($import->batch?->branch) ({{ $import->batch->branch->shortname }}) @endif
                </div>
                <div class="col-md-5">
                    <strong>Uploaded:</strong> {{ $import->created_at->format('d M Y, h:i A') }}
                    by {{ $import->uploader?->name ?? '—' }}
                </div>
            </div>

            @if ($import->sheet_title || $import->sheet_course)
                <div class="text-muted mt-2">
                    Heading in the sheet:
                    {{ collect([$import->sheet_title, $import->sheet_course])->filter()->implode(' / ') }}
                </div>
            @endif
        </div>
    </div>

    @unless ($import->course_matches)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            The sheet says <strong>{{ $import->sheet_course }}</strong>, but you selected
            <strong>{{ $import->course?->fullname }}</strong>. Check that you uploaded the right file for this course.
        </div>
    @endunless

    @php
        $tiles = [
            ['Rows in sheet', $import->total_rows, 'secondary'],
            ['Valid rows', $import->valid_rows, 'success'],
            ['Rows with errors', $import->error_rows, 'danger'],
            ['Valid, with warnings', $import->warning_rows, 'warning'],
        ];

        if (in_array($import->status, ['imported', 'cleared'])) {
            $tiles[] = ['Imported', $import->imported_rows, 'primary'];
            $tiles[] = ['Skipped', $import->skipped_rows, 'dark'];
        }
    @endphp

    <div class="row g-3 mb-3">
        @foreach ($tiles as [$label, $value, $color])
            <div class="col-6 col-md-2">
                <div class="card border-{{ $color }} mb-0">
                    <div class="card-body text-center py-2">
                        <div class="fs-3 fw-bold text-{{ $color }}">{{ $value }}</div>
                        <div class="small text-muted">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($import->status === 'preview')
        <div class="alert alert-info">
            <strong>Nothing has been saved yet.</strong>
            {{ $import->valid_rows }} of {{ $import->total_rows }} rows will be imported.
            @if ($import->error_rows)
                {{ $import->error_rows }} rows with errors will be skipped: fix them in the Excel file and upload again,
                or import only the valid rows.
            @endif
        </div>
    @elseif ($import->status === 'imported')
        <div class="alert alert-success">
            Imported on {{ $import->imported_at?->format('d M Y, h:i A') }}.
            If this sheet was wrong, use <strong>Clear this sheet</strong> below to remove its students.
        </div>
    @elseif ($import->status === 'cleared')
        <div class="alert alert-secondary">
            {{ $import->cleared_rows }} students were cleared on {{ $import->cleared_at?->format('d M Y, h:i A') }}
            by {{ $import->clearer?->name ?? '—' }}.
        </div>
    @else
        <div class="alert alert-secondary">This sheet was discarded. Its rows are not kept and nothing was saved.</div>
    @endif

    @if ($rows)
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                <span class="me-2 fw-bold">Show:</span>
                <button type="button" class="btn btn-sm btn-secondary filter-btn" data-filter="all">All ({{ $import->total_rows }})</button>
                <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="valid">Valid ({{ $import->valid_rows }})</button>
                <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="error">Errors ({{ $import->error_rows }})</button>
                <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="warning">Warnings ({{ $import->warning_rows }})</button>
            </div>

            <div class="card-body p-0 table-responsive" style="max-height:560px">
                <table class="table table-sm table-striped align-middle mb-0" id="previewTable">
                    <thead class="sticky-top bg-body">
                        <tr>
                            <th style="width:60px">Row</th>
                            <th>Roll No.</th>
                            <th>Enrollment No.</th>
                            <th>Student Name</th>
                            <th>Father</th>
                            <th>Mother</th>
                            <th>Address</th>
                            <th>Mobile</th>
                            <th style="width:110px">Status</th>
                            <th>Messages</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $row)
                        @php
                            $d = $row['data'];
                            $hasError = ! empty($row['errors']);
                            $hasWarning = ! empty($row['warnings']);
                            $result = $row['result'] ?? null;
                        @endphp
                        <tr data-error="{{ $hasError ? 1 : 0 }}" data-warning="{{ $hasWarning ? 1 : 0 }}"
                            class="{{ $hasError ? 'table-danger' : ($hasWarning ? 'table-warning' : '') }}">
                            <td>{{ $row['row'] }}</td>
                            <td>{{ $d['rollno'] }}</td>
                            <td>{{ $d['enrollmentno'] }}</td>
                            <td>{{ trim($d['firstname'] . ' ' . $d['lastname']) }}</td>
                            <td>{{ trim(($d['father_title'] ?? '') . ' ' . ($d['fathername'] ?? '')) }}</td>
                            <td>{{ trim(($d['mother_title'] ?? '') . ' ' . ($d['mothername'] ?? '')) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($d['address'], 30) }}</td>
                            <td>{{ $d['mobile'] }}</td>
                            <td>
                                @if ($result === 'imported')
                                    <span class="badge text-bg-success">Imported</span>
                                @elseif ($result === 'skipped')
                                    <span class="badge text-bg-secondary">Skipped</span>
                                @elseif ($hasError)
                                    <span class="badge text-bg-danger">Error</span>
                                @elseif ($hasWarning)
                                    <span class="badge text-bg-warning">Warning</span>
                                @else
                                    <span class="badge text-bg-success">Valid</span>
                                @endif
                            </td>
                            <td class="small">
                                @foreach ($row['errors'] as $message)
                                    <div class="text-danger">{{ $message }}</div>
                                @endforeach
                                @foreach ($row['warnings'] as $message)
                                    <div class="text-warning-emphasis">{{ $message }}</div>
                                @endforeach
                                @if (! empty($row['note']))
                                    <div class="text-muted">{{ $row['note'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                @if ($import->status === 'preview')
                    @can('studentimports.create')
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <form id="confirmForm" method="POST" action="{{ route('student-imports.confirm', $import) }}"
                                  class="d-flex flex-wrap align-items-center gap-3">
                                @csrf
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="verified" id="verified" value="1"
                                           @disabled($import->valid_rows === 0)>
                                    <label class="form-check-label" for="verified">
                                        I have checked the preview and the data is correct
                                    </label>
                                </div>
                                <button type="submit" id="confirmBtn" class="btn btn-success" disabled>
                                    <i class="bi bi-check2-circle"></i>
                                    Confirm &amp; Import {{ $import->valid_rows }} Students
                                </button>
                            </form>

                            <div class="d-flex gap-2">
                                <a href="{{ route('student-imports.create') }}" class="btn btn-outline-secondary">Upload another file</a>
                                <form method="POST" action="{{ route('student-imports.destroy', $import) }}"
                                      onsubmit="return confirm('Discard this sheet? Nothing has been saved yet.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger">Discard sheet</button>
                                </form>
                            </div>
                        </div>

                        @if ($import->valid_rows === 0)
                            <div class="text-danger mt-2">There are no valid rows to import. Fix the Excel file and upload it again.</div>
                        @endif
                    @endcan
                @elseif ($import->status === 'imported')
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('students.index', ['courseid' => $import->courseid, 'batchid' => $import->batchid]) }}"
                           class="btn btn-outline-primary">View students of this session</a>

                        @can('studentimports.clear')
                            <form method="POST" action="{{ route('student-imports.clear', $import) }}"
                                  onsubmit="return confirm('This removes the {{ $import->imported_rows }} students imported from this sheet from the student list. Continue?')">
                                @csrf
                                <button class="btn btn-danger"><i class="bi bi-eraser"></i> Clear this sheet</button>
                            </form>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <script>
            (function () {
                var rows = document.querySelectorAll('#previewTable tbody tr');
                var buttons = document.querySelectorAll('.filter-btn');

                // All / Valid / Errors / Warnings
                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var filter = button.dataset.filter;

                        rows.forEach(function (row) {
                            var error = row.dataset.error === '1';
                            var warning = row.dataset.warning === '1';
                            var show = filter === 'all'
                                || (filter === 'valid' && !error)
                                || (filter === 'error' && error)
                                || (filter === 'warning' && warning && !error);
                            row.style.display = show ? '' : 'none';
                        });

                        buttons.forEach(function (b) {
                            b.classList.toggle('btn-secondary', b === button);
                            b.classList.toggle('active', b === button);
                        });
                    });
                });

                // the import button works only after the "I have checked" box is ticked
                var verified = document.getElementById('verified');
                var confirmBtn = document.getElementById('confirmBtn');

                if (verified && confirmBtn) {
                    verified.addEventListener('change', function () { confirmBtn.disabled = !verified.checked; });
                    confirmBtn.closest('form').addEventListener('submit', function () { confirmBtn.disabled = true; });
                }
            })();
        </script>
    @endif
@stop