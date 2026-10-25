@php
    $footer = array_values((array) old('footer_items', $template->footer_items ?? []));
    $mode = old('margin_mode', $template->margin_mode ?? 'auto');
    $spacingMode = old('spacing_mode', $template->spacing_mode ?? 'auto');
    $layout = old('layout', $template->layout ?? 'semester');
    $flag = fn (string $name, $default = false) => (bool) old($name, $template->{$name} ?? $default);

    // the rows of "Where this template is used", restored after a validation error
    $assignRows = array_values(array_map(fn ($row) => [
        'courseid' => (int) ($row['courseid'] ?? 0),
        'batchid' => ($row['batchid'] ?? '') === '' || $row['batchid'] === null ? '' : (int) $row['batchid'],
    ], (array) old('assignments', $assignmentRows)));

    $courseOptions = $courses->map(fn ($c) => ['id' => $c->courseid, 'text' => $c->shortname . ' – ' . $c->fullname])->values()->all();
    $batchOptions = $batches->map(fn ($b) => [
        'id' => $b->batchid,
        'course' => $b->courseid,
        'text' => $b->label . ($b->branch ? ' – ' . $b->branch->shortname : ''),
    ])->values()->all();

    $infoRows = array_values((array) old('info_rows', $template->infoRows()));

    $spacing = [
        'header_top' => ['Space above the header', 'Extra space over the header, on top of the page margin.', 60],
        'header_side' => ['Header left / right inset', 'Pulls the header away from both side margins.', 60],
        'header_lines' => ['Gap between header lines', 'The space between two lines of the header.', 20],
        'after_header' => ['Gap between the header and the grade sheet', 'The space after the header (and its line) before the title.', 60],
        'sections' => ['Gap between sections', 'The space between the tables of the sheet.', 60],
        'footer' => ['Gap before the signatures', 'The space between the sheet and the signature line.', 60],
    ];
@endphp

{{-- general --}}
<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Template Name</label>
    <div class="col-sm-9">
        <input type="text" name="name" value="{{ old('name', $template->name) }}"
               class="form-control @error('name') is-invalid @enderror" placeholder="Grade sheet, School of Commerce" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Sheet type</label>
    <div class="col-sm-9">
        @foreach (\App\Models\Result\ResultTemplate::LAYOUTS as $value => $label)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="layout" id="layout_{{ $value }}" value="{{ $value }}" @checked($layout === $value)>
                <label class="form-check-label" for="layout_{{ $value }}">{{ $label }}</label>
            </div>
        @endforeach
        @error('layout') <div class="text-danger small">{{ $message }}</div> @enderror
        <div class="form-text">
            <strong>Semester grade sheet:</strong> header, student block, course table, semester-wise details and final result.<br>
            <strong>Ph.D. course work sheet:</strong> information table, course table, result lines, certificate and signatures.
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-switch d-inline-block me-4">
            <input type="hidden" name="is_default" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="is_default" id="is_default" value="1"
                   @checked($flag('is_default')) @disabled($template->exists && $template->is_default)>
            <label class="form-check-label" for="is_default">Default template</label>
        </div>
        <div class="form-check form-switch d-inline-block">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="active" id="active" value="1" @checked($flag('active', true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
        @if ($template->exists && $template->is_default)
            <div class="form-text">This is the default template. Make another template the default to change this.</div>
        @endif
    </div>
</div>

{{-- where it is used --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Where this template is used</h5>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div id="assignList"></div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addAssign">
            <i class="bi bi-plus-lg"></i> Add course / session
        </button>

        @foreach ($errors->keys() as $key)
            @if (str_starts_with($key, 'assignments'))
                @foreach ($errors->get($key) as $message)
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @endforeach
            @endif
        @endforeach

        <div class="form-text">
            One template can serve many courses and sessions. When a sheet is printed, the template of its
            <strong>course and session</strong> is used first, then the template of the <strong>course (all sessions)</strong>,
            then the <strong>default</strong> template. A course and session can belong to one template only.
        </div>
    </div>
</div>

{{-- sheet --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Sheet</h5>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Title</label>
    <div class="col-sm-5">
        <input type="text" name="title" value="{{ old('title', $template->title) }}"
               class="form-control @error('title') is-invalid @enderror" placeholder="GRADE-SHEET">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Leave empty to print no title.</div>
    </div>
    <div class="col-sm-2">@include('result_template._align', ['name' => 'title_align'])</div>
</div>

<div class="only-semester">
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Programme line</label>
        <div class="col-sm-5">
            <input type="text" name="programme_label" value="{{ old('programme_label', $template->programme_label) }}"
                   class="form-control @error('programme_label') is-invalid @enderror" placeholder="Name of the Programme:">
            @error('programme_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Leave empty to print only the course name.</div>
        </div>
        <div class="col-sm-2">@include('result_template._align', ['name' => 'programme_align'])</div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Exam label</label>
        <div class="col-sm-5">
            <input type="text" name="exam_label" value="{{ old('exam_label', $template->exam_label) }}"
                   class="form-control @error('exam_label') is-invalid @enderror" placeholder="Examination">
            @error('exam_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">For example "Examination: November-2023" or "Session: June-2025".</div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Date format</label>
    <div class="col-sm-3">
        <select name="date_format" class="form-select @error('date_format') is-invalid @enderror">
            @foreach (\App\Models\Result\ResultTemplate::DATE_FORMATS as $value => $example)
                <option value="{{ $value }}" @selected(old('date_format', $template->date_format) === $value)>{{ $example }}</option>
            @endforeach
        </select>
        @error('date_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Institute address</label>
    <div class="col-sm-9">
        <input type="text" name="institute_address" value="{{ old('institute_address', $template->institute_address) }}"
               class="form-control @error('institute_address') is-invalid @enderror" placeholder="Indore">
        @error('institute_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            Printed in the institute block of the semester sheet, and after the department name in the Ph.D. information table.
            Leave it empty to print the name only.
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="only-semester">
            @foreach ([
                'show_duration' => 'Add the course length after the programme name (for example "- 2 Years")',
                'show_semester_summary' => 'Semester-wise details table (I, II, III, IV)',
                'show_legend' => 'Line "SGPA: Semester Grade Point Average"',
                'show_final_result' => 'Final result block (total credits, CGPA, equivalent percentage, division) on the sheet of a final semester',
            ] as $name => $label)
                <div class="form-check form-switch">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" id="{{ $name }}" value="1"
                           @checked($flag($name, true))>
                    <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
                </div>
            @endforeach
            <div class="form-text">
                When the course name already has its length, like "M.B.A. (Foreign Trade)- 2 Years", nothing is added.
                The semesters where a final result prints are set under <strong>Result → Final Result Points</strong>.
            </div>
        </div>

        @foreach ([
            'show_repeat_note' => 'Mark grades of repeat examinations with * and print "*Grade in Repeat Examination" (on the Ph.D. sheet the note prints whenever this is on)',
            'show_declared_date' => 'Date of Result',
            'show_marks' => 'Marks columns (Internal, External, Total)',
        ] as $name => $label)
            <div class="form-check form-switch">
                <input type="hidden" name="{{ $name }}" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" id="{{ $name }}" value="1"
                       @checked($flag($name, in_array($name, ['show_repeat_note', 'show_declared_date'])))>
                <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
            </div>
        @endforeach
    </div>
</div>

{{-- Ph.D. sheet --}}
<div class="only-phd">
    <h5 class="mt-4 mb-3 border-bottom pb-2">Ph.D. sheet</h5>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Information table</label>
        <div class="col-sm-9">
            <div class="row g-2 fw-bold small mb-1">
                <div class="col-md-4">Shows</div>
                <div class="col-md-6">Label printed on the sheet</div>
            </div>

            <div id="infoList"></div>

            <button type="button" class="btn btn-sm btn-outline-primary" id="addInfo"><i class="bi bi-plus-lg"></i> Add row</button>
            <button type="button" class="btn btn-sm btn-light border" id="resetInfo"><i class="bi bi-arrow-counterclockwise"></i> Reset to the sample rows</button>

            @error('info_rows') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            <div class="form-text">
                The left column of the table is the label, the right column the value of the student.
                Use the arrows to change the order. A Subject or APAAR ID row is left out for a student who has no value.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Default subject</label>
        <div class="col-sm-4">
            <input type="text" name="default_subject" value="{{ old('default_subject', $template->default_subject) }}"
                   class="form-control @error('default_subject') is-invalid @enderror" placeholder="Commerce">
            @error('default_subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <label class="col-sm-2 col-form-label fw-bold text-sm-end">Session as</label>
        <div class="col-sm-3">
            <select name="session_format" class="form-select @error('session_format') is-invalid @enderror">
                @foreach (\App\Models\Result\ResultTemplate::SESSION_FORMATS as $value => $example)
                    <option value="{{ $value }}" @selected(old('session_format', $template->session_format) === $value)>{{ $example }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-9 offset-sm-3">
            <div class="form-text">
                The default subject is printed when a student has no Subject of his own (set it under Student → Edit).
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Result lines</label>
        <div class="col-sm-9">
            @foreach ([
                ['show_attempt_row', 'attempt_label', 'Attempt row (attempt and SGPA)', 'Attempt'],
                ['show_result_row', 'result_row_label', 'Result row (result, equivalent percentage and division)', 'Result'],
            ] as [$switch, $labelField, $text, $placeholder])
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-7">
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $switch }}" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="{{ $switch }}" id="{{ $switch }}" value="1" @checked($flag($switch, true))>
                            <label class="form-check-label" for="{{ $switch }}">{{ $text }}</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="{{ $labelField }}" value="{{ old($labelField, $template->{$labelField}) }}"
                               class="form-control form-control-sm @error($labelField) is-invalid @enderror" maxlength="30" placeholder="{{ $placeholder }}">
                    </div>
                </div>
            @endforeach

            @foreach ([
                ['show_equivalent', 'equivalent_label', 'Equivalent percentage (SGPA × ' . (float) config('result.cgpa_multiplier', 10) . ') for students who passed', 'Equivalent Percentage:-'],
                ['show_division', 'division_label', 'Division (from the Division Type of the session) for students who passed', 'Division:'],
            ] as [$switch, $labelField, $text, $placeholder])
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-7">
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $switch }}" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="{{ $switch }}" id="{{ $switch }}" value="1" @checked($flag($switch, true))>
                            <label class="form-check-label" for="{{ $switch }}">{{ $text }}</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="{{ $labelField }}" value="{{ old($labelField, $template->{$labelField}) }}"
                               class="form-control form-control-sm @error($labelField) is-invalid @enderror" maxlength="40" placeholder="{{ $placeholder }}">
                    </div>
                </div>
            @endforeach

            <div class="form-check form-switch mt-1">
                <input type="hidden" name="show_equivalent_note" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_equivalent_note" id="show_equivalent_note" value="1" @checked($flag('show_equivalent_note', true))>
                <label class="form-check-label" for="show_equivalent_note">Note "Equivalent Percentage=SGPA*{{ (float) config('result.cgpa_multiplier', 10) }}" under the course table</label>
            </div>
            <div class="form-text">
                The result words (PASS, PROMOTED, FAIL) and the division ranges come from the Session Grade and Division settings of the session.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Certificate</label>
        <div class="col-sm-9">
            <div class="form-check form-switch">
                <input type="hidden" name="show_certificate" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_certificate" id="show_certificate" value="1" @checked($flag('show_certificate', true))>
                <label class="form-check-label" for="show_certificate">Print the certificate text</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="hidden" name="certificate_pass_only" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="certificate_pass_only" id="certificate_pass_only" value="1" @checked($flag('certificate_pass_only', true))>
                <label class="form-check-label" for="certificate_pass_only">Only for students who passed</label>
            </div>

            <textarea name="certificate_text" rows="3" class="form-control @error('certificate_text') is-invalid @enderror">{{ old('certificate_text', $template->certificate_text) }}</textarea>
            @error('certificate_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">
                Placeholders: <code>{name}</code> <code>{course}</code> <code>{roll}</code> <code>{enrollment}</code> <code>{session}</code>
            </div>
        </div>
    </div>
</div>

{{-- institute block (semester sheet) --}}
<div class="only-semester">
    <h5 class="mt-4 mb-3 border-bottom pb-2">Institute block</h5>

    <div class="row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <div class="form-check form-switch">
                <input type="hidden" name="show_institute_block" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_institute_block" id="show_institute_block" value="1" @checked($flag('show_institute_block'))>
                <label class="form-check-label fw-bold" for="show_institute_block">Print the institute block</label>
            </div>
            <div class="form-text">
                Prints "Name of the Institute / School of Studies : School Of Commerce" (the department of the course)
                and, below it, the address line.
            </div>
        </div>
    </div>

    <div id="instituteBox">
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Name line label</label>
            <div class="col-sm-5">
                <input type="text" name="institute_label" value="{{ old('institute_label', $template->institute_label) }}"
                       class="form-control @error('institute_label') is-invalid @enderror">
                @error('institute_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-2">@include('result_template._align', ['name' => 'institute_align'])</div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Address line label</label>
            <div class="col-sm-5">
                <input type="text" name="address_label" value="{{ old('address_label', $template->address_label) }}"
                       class="form-control @error('address_label') is-invalid @enderror">
                @error('address_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

{{-- header --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Header</h5>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_header" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="show_header" id="show_header" value="1" @checked($flag('show_header', true))>
            <label class="form-check-label fw-bold" for="show_header">Print the header</label>
        </div>
        <div class="form-text">Switch it off to print on pre-printed letterhead paper (the Ph.D. sample has no header).</div>
    </div>
</div>

<div id="headerBox">
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">University / Institute</label>
        <div class="col-sm-7">
            <input type="text" name="university_name" value="{{ old('university_name', $template->university_name) }}"
                   class="form-control @error('university_name') is-invalid @enderror" placeholder="DEVI AHILYA VISHWAVIDYALAYA, INDORE">
            @error('university_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-sm-2" title="Alignment of all the header text">@include('result_template._align', ['name' => 'header_align'])</div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Lines above the department</label>
        <div class="col-sm-9">
            <textarea name="header_lines_top" rows="2" class="form-control @error('header_lines_top') is-invalid @enderror"
                      placeholder="NAAC Accredited 'A' Grade State University">{{ old('header_lines_top', $template->header_lines_top) }}</textarea>
            @error('header_lines_top') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">One line per row.</div>
        </div>
    </div>

    <div class="only-semester">
        <div class="row mb-3">
            <div class="col-sm-9 offset-sm-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_department" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="show_department" id="show_department" value="1" @checked($flag('show_department', true))>
                    <label class="form-check-label" for="show_department">Print the department name (for example SCHOOL OF COMMERCE)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Lines below the department</label>
        <div class="col-sm-9">
            <textarea name="header_lines_bottom" rows="2" class="form-control @error('header_lines_bottom') is-invalid @enderror"
                      placeholder="UGC-SAP DEPARTMENT&#10;Takshashila Campus, Khandwa Road, Indore">{{ old('header_lines_bottom', $template->header_lines_bottom) }}</textarea>
            @error('header_lines_bottom') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Logo</label>
        <div class="col-sm-9">
            <input type="file" name="logo" accept=".png,.jpg,.jpeg" class="form-control @error('logo') is-invalid @enderror">
            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">PNG or JPG, up to 1 MB.</div>

            @if ($template->logo_url)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ $template->logo_url }}" alt="logo" style="height:60px">
                    <div class="form-check mb-0">
                        <input type="hidden" name="remove_logo" value="0">
                        <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                        <label class="form-check-label" for="remove_logo">Remove the logo</label>
                    </div>
                </div>
            @endif

            <div class="d-flex align-items-center gap-3 mt-2">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="show_logo" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="show_logo" id="show_logo" value="1" @checked($flag('show_logo', true))>
                    <label class="form-check-label" for="show_logo">Print the logo</label>
                </div>
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text">Side</span>
                    <select name="logo_position" class="form-select form-select-sm">
                        @foreach (\App\Models\Result\ResultTemplate::SIDES as $value => $label)
                            <option value="{{ $value }}" @selected(old('logo_position', $template->logo_position) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <div class="form-check form-switch">
                <input type="hidden" name="show_header_line" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_header_line" id="show_header_line" value="1" @checked($flag('show_header_line', true))>
                <label class="form-check-label" for="show_header_line">Line under the header</label>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="show_photo" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_photo" id="show_photo" value="1" @checked($flag('show_photo'))>
                <label class="form-check-label" for="show_photo">Print the student photo</label>
            </div>
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text">Side</span>
                <select name="photo_position" class="form-select form-select-sm">
                    @foreach (\App\Models\Result\ResultTemplate::SIDES as $value => $label)
                        <option value="{{ $value }}" @selected(old('photo_position', $template->photo_position) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-text">A student without a photo prints without one.</div>
    </div>
</div>

{{-- footer --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Footer</h5>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="show_footer" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="show_footer" id="show_footer" value="1" @checked($flag('show_footer', true))>
            <label class="form-check-label fw-bold" for="show_footer">Print the signature line</label>
        </div>

        <div id="footerBox">
            <div id="footerList" class="mb-2"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addFooter"><i class="bi bi-plus-lg"></i> Add</button>
            <span class="small text-muted ms-2">Quick add:</span>
            @foreach (\App\Models\Result\ResultTemplate::SIGNATURES as $label)
                <button type="button" class="btn btn-sm btn-light border quick-sign" data-label="{{ $label }}">{{ $label }}</button>
            @endforeach
            @error('footer_items') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('footer_items.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            <div class="form-text">Up to {{ \App\Models\Result\ResultTemplate::MAX_SIGNATURES }} labels, printed left to right.</div>
        </div>
    </div>
</div>

{{-- spacing --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Spacing</h5>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="spacing_mode" id="spacingAuto" value="auto" @checked($spacingMode === 'auto')>
            <label class="form-check-label" for="spacingAuto">Auto</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="spacing_mode" id="spacingCustom" value="custom" @checked($spacingMode === 'custom')>
            <label class="form-check-label" for="spacingCustom">Custom (mm)</label>
        </div>

        <div id="spacingBox" class="mt-2">
            @foreach ($spacing as $name => [$label, $hint, $max])
                @php
                    $field = "space_{$name}";
                    $current = old($field, $template->{$field} !== null ? (float) $template->{$field} : '');
                @endphp
                <div class="row g-2 align-items-center mb-2">
                    <label class="col-md-5 col-form-label" for="{{ $field }}" title="{{ $hint }}">{{ $label }}</label>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.5" min="0" max="{{ $max }}" id="{{ $field }}" name="{{ $field }}" value="{{ $current }}"
                                   class="form-control @error($field) is-invalid @enderror"
                                   placeholder="auto: {{ \App\Models\Result\ResultTemplate::AUTO_SPACING[$name] }}">
                            <span class="input-group-text">mm</span>
                        </div>
                        @error($field) <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 small text-muted">{{ $hint }}</div>
                </div>
            @endforeach
            <div class="form-text">An empty box keeps its automatic value.</div>
        </div>
    </div>
</div>

{{-- margins --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Page margins</h5>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="margin_mode" id="marginAuto" value="auto" @checked($mode === 'auto')>
            <label class="form-check-label" for="marginAuto">Auto (top and bottom 14 mm, sides 12 mm)</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="margin_mode" id="marginCustom" value="custom" @checked($mode === 'custom')>
            <label class="form-check-label" for="marginCustom">Custom (mm)</label>
        </div>

        <div class="row g-2 mt-1" id="marginBox">
            @foreach (['top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left'] as $side => $label)
                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ $label }}</span>
                        <input type="number" min="0" max="60" name="margin_{{ $side }}"
                               value="{{ old("margin_{$side}", $template->{"margin_{$side}"}) }}"
                               class="form-control @error("margin_{$side}") is-invalid @enderror" placeholder="mm">
                    </div>
                    @error("margin_{$side}") <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            @endforeach
        </div>
        <div class="form-text">Use a bigger top margin when you print on letterhead paper with the header switched off.</div>
    </div>
</div>

<script>
    (function () {
        var max = {{ \App\Models\Result\ResultTemplate::MAX_SIGNATURES }};
        var list = document.getElementById('footerList');

        function option(value, text, selected) {
            var o = document.createElement('option');
            o.value = value;
            o.textContent = text;
            if (selected) o.selected = true;
            return o;
        }

        // ---- sheet type: the sections that do not apply are hidden (their values are kept)
        function layout() {
            var phd = document.getElementById('layout_phd').checked;

            document.querySelectorAll('.only-phd').forEach(function (el) { el.style.display = phd ? '' : 'none'; });
            document.querySelectorAll('.only-semester').forEach(function (el) { el.style.display = phd ? 'none' : ''; });
        }

        document.getElementById('layout_semester').addEventListener('change', layout);
        document.getElementById('layout_phd').addEventListener('change', layout);
        layout();

        // ---- where the template is used
        var courses = @json($courseOptions);
        var batches = @json($batchOptions);
        var assigned = @json($assignRows);
        var assignList = document.getElementById('assignList');
        var assignCounter = 0;

        // the sessions of the chosen course
        function fillBatches(select, courseId, selected) {
            select.innerHTML = '';
            select.appendChild(option('', 'All sessions', selected === '' || selected === null));

            batches.filter(function (b) { return String(b.course) === String(courseId); })
                .forEach(function (b) { select.appendChild(option(b.id, b.text, String(b.id) === String(selected))); });
        }

        function addAssign(courseId, batchId) {
            var index = assignCounter++;

            var row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-center';

            var colCourse = document.createElement('div');
            colCourse.className = 'col-md-6';
            var course = document.createElement('select');
            course.name = 'assignments[' + index + '][courseid]';
            course.className = 'form-select form-select-sm';
            course.appendChild(option('', 'Select course', !courseId));
            courses.forEach(function (c) { course.appendChild(option(c.id, c.text, String(c.id) === String(courseId))); });
            colCourse.appendChild(course);

            var colBatch = document.createElement('div');
            colBatch.className = 'col-md-4';
            var batch = document.createElement('select');
            batch.name = 'assignments[' + index + '][batchid]';
            batch.className = 'form-select form-select-sm';
            fillBatches(batch, courseId, batchId === undefined ? '' : batchId);
            colBatch.appendChild(batch);

            course.addEventListener('change', function () { fillBatches(batch, course.value, ''); });

            var colRemove = document.createElement('div');
            colRemove.className = 'col-md-1';
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.title = 'Remove';
            remove.addEventListener('click', function () { row.remove(); });
            colRemove.appendChild(remove);

            row.appendChild(colCourse);
            row.appendChild(colBatch);
            row.appendChild(colRemove);
            assignList.appendChild(row);
        }

        assigned.forEach(function (row) { addAssign(row.courseid || '', row.batchid); });
        document.getElementById('addAssign').addEventListener('click', function () { addAssign('', ''); });

        // ---- the Ph.D. information table
        var infoFields = @json(\App\Models\Result\ResultTemplate::INFO_FIELDS);
        var infoDefaults = @json(\App\Models\Result\ResultTemplate::DEFAULT_INFO_ROWS);
        var infoRows = @json($infoRows);
        var infoList = document.getElementById('infoList');
        var infoCounter = 0;

        function addInfo(row) {
            var index = infoCounter++;
            var current = row && row.field ? row.field : 'name';

            var group = document.createElement('div');
            group.className = 'row g-2 mb-1 align-items-center';

            var colField = document.createElement('div');
            colField.className = 'col-md-4';
            var select = document.createElement('select');
            select.name = 'info_rows[' + index + '][field]';
            select.className = 'form-select form-select-sm';
            Object.keys(infoFields).forEach(function (key) { select.appendChild(option(key, infoFields[key], key === current)); });
            colField.appendChild(select);

            var colLabel = document.createElement('div');
            colLabel.className = 'col-md-6';
            var label = document.createElement('input');
            label.type = 'text';
            label.name = 'info_rows[' + index + '][label]';
            label.maxLength = 160;
            label.className = 'form-control form-control-sm';
            label.value = row && row.label ? row.label : infoFields[current];
            colLabel.appendChild(label);

            // choosing another field proposes its standard label, unless the label was typed by hand
            select.addEventListener('change', function () {
                if (label.value.trim() === '' || label.dataset.auto === '1' || label.value === infoFields[current]) {
                    label.value = infoFields[select.value];
                    label.dataset.auto = '1';
                }
                current = select.value;
            });
            label.addEventListener('input', function () { label.dataset.auto = '0'; });

            var colTools = document.createElement('div');
            colTools.className = 'col-md-2 text-nowrap';

            function tool(icon, title, action) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn btn-sm btn-outline-secondary me-1';
                b.innerHTML = '<i class="bi ' + icon + '"></i>';
                b.title = title;
                b.addEventListener('click', action);
                return b;
            }

            colTools.appendChild(tool('bi-arrow-up', 'Move up', function () {
                var previous = group.previousElementSibling;
                if (previous) infoList.insertBefore(group, previous);
            }));
            colTools.appendChild(tool('bi-arrow-down', 'Move down', function () {
                var next = group.nextElementSibling;
                if (next) infoList.insertBefore(next, group);
            }));
            colTools.appendChild(tool('bi-x-lg', 'Remove', function () { group.remove(); }));

            group.appendChild(colField);
            group.appendChild(colLabel);
            group.appendChild(colTools);
            infoList.appendChild(group);
        }

        infoRows.forEach(addInfo);
        document.getElementById('addInfo').addEventListener('click', function () { addInfo(null); });
        document.getElementById('resetInfo').addEventListener('click', function () {
            infoList.innerHTML = '';
            infoDefaults.forEach(addInfo);
        });

        // ---- a switched-off area is dimmed (not disabled), so nothing typed in it is lost
        function dim(switchId, boxId) {
            var box = document.getElementById(boxId);
            var toggle = document.getElementById(switchId);
            var sync = function () { box.style.opacity = toggle.checked ? '1' : '0.45'; };
            toggle.addEventListener('change', sync);
            sync();
        }

        dim('show_header', 'headerBox');
        dim('show_footer', 'footerBox');
        dim('show_institute_block', 'instituteBox');

        // the number boxes show only in custom mode
        function modeBox(autoId, customId, boxId) {
            var sync = function () { document.getElementById(boxId).style.display = document.getElementById(customId).checked ? '' : 'none'; };
            document.getElementById(autoId).addEventListener('change', sync);
            document.getElementById(customId).addEventListener('change', sync);
            sync();
        }

        modeBox('marginAuto', 'marginCustom', 'marginBox');
        modeBox('spacingAuto', 'spacingCustom', 'spacingBox');

        // ---- signature labels
        function addRow(value) {
            if (list.children.length >= max) return;

            var group = document.createElement('div');
            group.className = 'input-group input-group-sm mb-1';

            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'footer_items[]';
            input.maxLength = 40;
            input.value = value || '';
            input.className = 'form-control';
            input.placeholder = 'Co-ordinator';

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-outline-danger';
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.title = 'Remove';
            remove.addEventListener('click', function () { group.remove(); });

            group.appendChild(input);
            group.appendChild(remove);
            list.appendChild(group);
        }

        @json($footer).forEach(addRow);

        document.getElementById('addFooter').addEventListener('click', function () { addRow(''); });

        document.querySelectorAll('.quick-sign').forEach(function (button) {
            button.addEventListener('click', function () {
                var taken = Array.prototype.some.call(list.querySelectorAll('input'), function (i) { return i.value === button.dataset.label; });
                if (!taken) addRow(button.dataset.label);
            });
        });
    })();
</script>