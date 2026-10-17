<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Department</label>
    <div class="col-sm-9">
        <select name="deptid" class="form-select @error('deptid') is-invalid @enderror" required>
            <option value="">Select Department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->deptid }}"
                    @selected(old('deptid', $course->deptid) == $department->deptid)>
                    {{ $department->fullname }}
                </option>
            @endforeach
        </select>
        @error('deptid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course Type</label>
    <div class="col-sm-9">
        <select name="coursetypeid" class="form-select @error('coursetypeid') is-invalid @enderror" required>
            <option value="">Select Course Type</option>
            @foreach ($courseTypes as $courseType)
                <option value="{{ $courseType->coursetypeid }}"
                    @selected(old('coursetypeid', $course->coursetypeid) == $courseType->coursetypeid)>
                    {{ $courseType->coursetypename }}
                </option>
            @endforeach
        </select>
        @error('coursetypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course Name</label>
    <div class="col-sm-9">
        <input type="text" name="shortname" value="{{ old('shortname', $course->shortname) }}"
               class="form-control @error('shortname') is-invalid @enderror" placeholder="MBA" required>
        @error('shortname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course Full Name</label>
    <div class="col-sm-9">
        <input type="text" name="fullname" value="{{ old('fullname', $course->fullname) }}"
               class="form-control @error('fullname') is-invalid @enderror"
               placeholder="Master of Business Administration" required>
        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Academic Structure</label>
    <div class="col-sm-9">
        <select name="structure_type" id="structure_type"
                class="form-select @error('structure_type') is-invalid @enderror" required>
            @foreach ($structures as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('structure_type', $course->structure_type) == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('structure_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end" id="terms_label">Max Semester</label>
    <div class="col-sm-9">
        <input type="number" name="total_terms" id="total_terms" min="1" max="12"
               value="{{ old('total_terms', $course->total_terms) }}"
               class="form-control @error('total_terms') is-invalid @enderror" placeholder="4" required>
        @error('total_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="has_branch" value="0">
            <input class="form-check-input" type="checkbox" name="has_branch" id="has_branch" value="1"
                   @checked(old('has_branch', $course->has_branch))>
            <label class="form-check-label" for="has_branch">
                This course has branches (for example CSE, Mechanical)
            </label>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <textarea name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $course->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $course->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

<script>
    // Switch the label and the allowed maximum when the structure changes
    (function () {
        var type = document.getElementById('structure_type');
        var label = document.getElementById('terms_label');
        var terms = document.getElementById('total_terms');

        function sync() {
            var isYear = type.value === 'year';
            label.textContent = isYear ? 'Max Years' : 'Max Semester';
            terms.max = isYear ? 6 : 12;
        }

        type.addEventListener('change', sync);
        sync();
    })();
</script>