@php
    $footer = array_values((array) old('footer_items', $template->footer_items ?? []));
    $mode = old('margin_mode', $template->margin_mode ?? 'auto');
    $flag = fn (string $name, $default = false) => (bool) old($name, $template->{$name} ?? $default);
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
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Use for course</label>
    <div class="col-sm-9">
        <select name="courseid" class="form-select @error('courseid') is-invalid @enderror">
            <option value="">All courses</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}" @selected(old('courseid', $template->courseid) == $course->courseid)>
                    {{ $course->shortname }} – {{ $course->fullname }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">A course's own template is used first; every other course uses the default template.</div>
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

{{-- sheet --}}
<h5 class="mt-4 mb-3 border-bottom pb-2">Sheet</h5>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Title</label>
    <div class="col-sm-4">
        <input type="text" name="title" value="{{ old('title', $template->title) }}"
               class="form-control @error('title') is-invalid @enderror" placeholder="GRADE-SHEET" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Programme label</label>
    <div class="col-sm-3">
        <input type="text" name="programme_label" value="{{ old('programme_label', $template->programme_label) }}"
               class="form-control @error('programme_label') is-invalid @enderror" required>
        @error('programme_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        @foreach ([
            'show_semester_summary' => 'Semester-wise details table (I, II, III, IV)',
            'show_legend' => 'Line "SGPA: Semester Grade Point Average"',
            'show_declared_date' => 'Date of Result',
            'show_marks' => 'Marks columns (Internal, External, Total)',
            'show_institute_block' => 'Institute block ("Name of the Institute / School of Studies", address)',
        ] as $name => $label)
            <div class="form-check form-switch">
                <input type="hidden" name="{{ $name }}" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" id="{{ $name }}" value="1"
                       @checked($flag($name, in_array($name, ['show_semester_summary', 'show_legend', 'show_declared_date'])))>
                <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
            </div>
        @endforeach

        <input type="text" name="institute_address" value="{{ old('institute_address', $template->institute_address) }}"
               class="form-control mt-2 @error('institute_address') is-invalid @enderror"
               placeholder="Address of the institute (used by the institute block)">
        @error('institute_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <div class="col-sm-9">
            <input type="text" name="university_name" value="{{ old('university_name', $template->university_name) }}"
                   class="form-control @error('university_name') is-invalid @enderror" placeholder="DEVI AHILYA VISHWAVIDYALAYA, INDORE">
            @error('university_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
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

            <div class="form-check form-switch mt-2">
                <input type="hidden" name="show_logo" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="show_logo" id="show_logo" value="1" @checked($flag('show_logo', true))>
                <label class="form-check-label" for="show_logo">Print the logo</label>
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
        <div class="form-check form-switch">
            <input type="hidden" name="show_photo" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="show_photo" id="show_photo" value="1" @checked($flag('show_photo'))>
            <label class="form-check-label" for="show_photo">Print the student photo (top right)</label>
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

        // the header, footer and margin boxes are dimmed (not disabled) when switched off, so nothing is lost
        function dim(switchId, boxId) {
            var box = document.getElementById(boxId);
            var toggle = document.getElementById(switchId);
            var sync = function () { box.style.opacity = toggle.checked ? '1' : '0.45'; };
            toggle.addEventListener('change', sync);
            sync();
        }

        dim('show_header', 'headerBox');
        dim('show_footer', 'footerBox');

        function margins() {
            var custom = document.getElementById('marginCustom').checked;
            document.getElementById('marginBox').style.display = custom ? '' : 'none';
        }
        document.getElementById('marginAuto').addEventListener('change', margins);
        document.getElementById('marginCustom').addEventListener('change', margins);
        margins();

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