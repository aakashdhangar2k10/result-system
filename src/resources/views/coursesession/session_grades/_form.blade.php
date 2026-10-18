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