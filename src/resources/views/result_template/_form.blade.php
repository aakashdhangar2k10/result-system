@php
    $footer = array_values((array) old('footer_items', $template->footer_items ?? []));
    $mode = old('margin_mode', $template->margin_mode ?? 'auto');
    $spacingMode = old('spacing_mode', $template->spacing_mode ?? 'auto');
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

    $spacing = [
        'header_top' => ['Space above the header', 'Extra space over the header, on top of the page margin.', 60],
        'header_side' => ['Header left / right inset', 'Pulls the header away from both side margins.', 60],
        'header_lines' => ['Gap between header lines', 'The space between two lines of the header.', 20],
        'after_header' => ['Gap between the header and the grade sheet', 'The space after the header (and its line) before the title.', 60],
        'sections' => ['Gap between sections', 'The space between the student block, the course table and the semester table.', 60],
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
    <div class="col-sm-3">
        <input type="text" name="exam_label" value="{{ old('exam_label', $template->exam_label) }}"
               class="form-control @error('exam_label') is-invalid @enderror" placeholder="Examination" required>
        @error('exam_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">For example "Examination: November-2023" or "Session: June-2025".</div>
    </div>
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Date format</label>
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
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_duration" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="show_duration" id="show_duration" value="1" @checked($flag('show_duration', true))>
            <label class="form-check-label" for="show_duration">Add the course length after the programme name (for example "- 2 Years")</label>
        </div>
        <div class="form-text">When the course name already has its length, like "M.B.A. (Foreign Trade)- 2 Years", nothing is added.</div>

        @foreach ([
            'show_semester_summary' => 'Semester-wise details table (I, II, III, IV)',
            'show_legend' => 'Line "SGPA: Semester Grade Point Average"',
            'show_final_result' => 'Final result block (total credits, CGPA, equivalent percentage, division) on the sheet of a final semester',
            'show_repeat_note' => 'Mark grades of repeat examinations with * and print "* Grade in Repeat Examination"',
            'show_declared_date' => 'Date of Result',
            'show_marks' => 'Marks columns (Internal, External, Total)',
        ] as $name => $label)
            <div class="form-check form-switch">
                <input type="hidden" name="{{ $name }}" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" id="{{ $name }}" value="1"
                       @checked($flag($name, in_array($name, ['show_semester_summary', 'show_legend', 'show_final_result', 'show_repeat_note', 'show_declared_date'])))>
                <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
            </div>
        @endforeach
        <div class="form-text">
            The semesters where a final result prints are set under <strong>Result → Final Result Points</strong>.
        </div>
    </div>
</div>

{{-- institute block --}}
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
                   class="form-control @error('institute_label') is-invalid @enderror" required>
            @error('institute_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-sm-2">@include('result_template._align', ['name' => 'institute_align'])</div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Address line label</label>
        <div class="col-sm-5">
            <input type="text" name="address_label" value="{{ old('address_label', $template->address_label) }}"
                   class="form-control @error('address_label') is-invalid @enderror" required>
            @error('address_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label fw-bold text-sm-end">Address</label>
        <div class="col-sm-9">
            <input type="text" name="institute_address" value="{{ old('institute_address', $template->institute_address) }}"
                   class="form-control @error('institute_address') is-invalid @enderror" placeholder="Indore">
            @error('institute_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Leave it empty to print the name line only.</div>
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
        <div class="form-text">Switch it off to print on pre-printed letterhead paper.</div>
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

    <div class="row mb-3">
        <div class="col-sm-9 offset-sm-3">
            <div class="form-check form-switch">
                <input type="hidden" name="show_department" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_department" id="show_department" value="1" @checked($flag('show_department', true))>
                <label class="form-check-label" for="show_department">Print the department name (for example SCHOOL OF COMMERCE)</label>
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

        // ---- where the template is used
        var courses = @json($courseOptions);
        var batches = @json($batchOptions);
        var assigned = @json($assignRows);
        var assignList = document.getElementById('assignList');
        var counter = 0;

        function option(value, text, selected) {
            var o = document.createElement('option');
            o.value = value;
            o.textContent = text;
            if (selected) o.selected = true;
            return o;
        }

        // the sessions of the chosen course
        function fillBatches(select, courseId, selected) {
            select.innerHTML = '';
            select.appendChild(option('', 'All sessions', selected === '' || selected === null));

            batches.filter(function (b) { return String(b.course) === String(courseId); })
                .forEach(function (b) { select.appendChild(option(b.id, b.text, String(b.id) === String(selected))); });
        }

        function addAssign(courseId, batchId) {
            var index = counter++;

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

        // a switched-off area is dimmed (not disabled), so nothing typed in it is lost
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

        // signature labels
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