<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}"
                        data-coursetype="{{ $course->coursetypeid }}"
                        @selected(old('courseid', $sessionGrade->courseid) == $course->courseid)>
                    {{ $course->shortname }} – {{ $course->fullname }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
    <div class="col-sm-9">
        <select name="batchid" id="batchid" class="form-select @error('batchid') is-invalid @enderror" required>
            <option value="">Select Session</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                    @selected(old('batchid', $sessionGrade->batchid) == $batch->batchid)>
                    {{ $batch->label }}{{ $batch->branch ? ' – ' . $batch->branch->shortname : '' }}
                </option>
            @endforeach
        </select>
        @error('batchid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Grade Type</label>
    <div class="col-sm-9">
        <select name="gradetypeid" id="gradetypeid" class="form-select @error('gradetypeid') is-invalid @enderror" required>
            <option value="">Select Grade Type</option>
            @foreach ($gradeTypes as $gradeType)
                <option value="{{ $gradeType->coursetypeid }}"
                    @selected(old('gradetypeid', $sessionGrade->gradetypeid) == $gradeType->coursetypeid)>
                    {{ $gradeType->coursetypename }}
                </option>
            @endforeach
        </select>
        @error('gradetypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Division Type</label>
    <div class="col-sm-9">
        <select name="divtypeid" class="form-select @error('divtypeid') is-invalid @enderror" required>
            <option value="">Select Division Type</option>
            @foreach ($divisionTypes as $divisionType)
                <option value="{{ $divisionType->divtypeid }}"
                    @selected(old('divtypeid', $sessionGrade->divtypeid) == $divisionType->divtypeid)>
                    {{ $divisionType->divtypename }}
                </option>
            @endforeach
        </select>
        @error('divtypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <textarea name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $sessionGrade->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<h5 class="mt-4 mb-3 border-bottom pb-2">Result rule <small class="text-muted fw-normal">(decides PASS / PROMOTED / FAIL of a semester)</small></h5>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Backlogs allowed</label>
    <div class="col-sm-9">
        <input type="number" name="max_backlogs" id="max_backlogs" min="0" max="20"
               value="{{ old('max_backlogs', $sessionGrade->max_backlogs ?? 0) }}"
               class="form-control @error('max_backlogs') is-invalid @enderror" required>
        @error('max_backlogs') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            Failed subjects (backlogs) a student may carry and still be promoted to the next semester.
            <strong>0</strong> means the student must pass every subject.
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Minimum SGPA</label>
    <div class="col-sm-9">
        <input type="number" name="min_sgpa" id="min_sgpa" step="0.01" min="0" max="10"
               value="{{ old('min_sgpa', $sessionGrade->min_sgpa) }}"
               class="form-control @error('min_sgpa') is-invalid @enderror" placeholder="Leave empty for no minimum">
        @error('min_sgpa') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">A student whose SGPA is below this is FAIL, whatever the backlogs.</div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Result words</label>
    <div class="col-sm-9">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="pass_label" id="pass_label" maxlength="30"
                       value="{{ old('pass_label', $sessionGrade->pass_label ?? 'PASS') }}"
                       class="form-control @error('pass_label') is-invalid @enderror" placeholder="PASS" required>
                @error('pass_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <input type="text" name="promoted_label" id="promoted_label" maxlength="30"
                       value="{{ old('promoted_label', $sessionGrade->promoted_label ?? 'PROMOTED') }}"
                       class="form-control @error('promoted_label') is-invalid @enderror" placeholder="PROMOTED" required>
                @error('promoted_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <input type="text" name="fail_label" id="fail_label" maxlength="30"
                       value="{{ old('fail_label', $sessionGrade->fail_label ?? 'FAIL') }}"
                       class="form-control @error('fail_label') is-invalid @enderror" placeholder="FAIL" required>
                @error('fail_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="form-text">The three words printed in the Result column, for example PASS, ATKT, DETAINED.</div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="sgpa_with_failed" value="0">
            <input class="form-check-input" type="checkbox" name="sgpa_with_failed" id="sgpa_with_failed" value="1"
                   @checked(old('sgpa_with_failed', $sessionGrade->sgpa_with_failed ?? true))>
            <label class="form-check-label" for="sgpa_with_failed">
                Count the credits of failed subjects in the SGPA (with 0 grade points)
            </label>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="border rounded p-3 bg-body-tertiary">
            <div class="fw-bold mb-2">This rule means</div>
            <div id="rulePreview"></div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $sessionGrade->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

<script>
    (function () {
        var field = function (id) { return document.getElementById(id); };

        function render() {
            var max = parseInt(field('max_backlogs').value || '0', 10);
            var min = field('min_sgpa').value.trim();
            var gate = min !== '' ? ' and SGPA is at least ' + parseFloat(min) : '';
            var over = max === 0 ? 'any backlog' : 'more than ' + max + (max === 1 ? ' backlog' : ' backlogs');

            var lines = [['success', field('pass_label').value, 'no backlog' + gate]];
            if (max > 0) {
                lines.push(['primary', field('promoted_label').value,
                    (max === 1 ? '1 backlog' : '1 to ' + max + ' backlogs') + gate]);
            }
            lines.push(['danger', field('fail_label').value, over + (min !== '' ? ' or SGPA below ' + parseFloat(min) : '')]);

            var box = field('rulePreview');
            box.innerHTML = '';

            lines.forEach(function (line) {
                var row = document.createElement('div');
                row.className = 'mb-1';

                var badge = document.createElement('span');
                badge.className = 'badge text-bg-' + line[0] + ' me-2';
                badge.textContent = line[1] || '—';

                row.appendChild(badge);
                row.appendChild(document.createTextNode(line[2]));
                box.appendChild(row);
            });
        }

        ['max_backlogs', 'min_sgpa', 'pass_label', 'promoted_label', 'fail_label'].forEach(function (id) {
            field(id).addEventListener('input', render);
        });

        render();
    })();
</script>

<script>
    (function () {
        var course = document.getElementById('courseid');
        var batch = document.getElementById('batchid');
        var gradeType = document.getElementById('gradetypeid');

        // a grade type that is already chosen (edit page, or after an error) is never overwritten
        var gradeTouched = gradeType.value !== '';
        gradeType.addEventListener('change', function () { gradeTouched = true; });

        function sync() {
            var opt = course.options[course.selectedIndex];
            var courseId = opt ? opt.value : '';

            // show only the sessions of the selected course
            Array.prototype.forEach.call(batch.options, function (o) {
                if (!o.value) return;
                var match = courseId !== '' && o.dataset.course === courseId;
                o.hidden = !match;
                o.disabled = !match;
            });

            var chosen = batch.options[batch.selectedIndex];
            if (chosen && chosen.disabled) batch.value = '';

            // default the grade type to the course's own course type
            if (!gradeTouched && opt && opt.dataset.coursetype) {
                gradeType.value = opt.dataset.coursetype;
            }
        }

        course.addEventListener('change', sync);
        sync();
    })();
</script>