<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}"
                    @selected(old('courseid', $branch->courseid) == $course->courseid)>
                    {{ $course->shortname }} – {{ $course->fullname }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($courses->isEmpty())
            <div class="form-text text-danger">
                No course has branches enabled yet. Edit a course and tick "This course has branches".
            </div>
        @endif
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Branch Name</label>
    <div class="col-sm-9">
        <input type="text" name="shortname" value="{{ old('shortname', $branch->shortname) }}"
               class="form-control @error('shortname') is-invalid @enderror" placeholder="CSE" required>
        @error('shortname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Branch Full Name</label>
    <div class="col-sm-9">
        <input type="text" name="fullname" value="{{ old('fullname', $branch->fullname) }}"
               class="form-control @error('fullname') is-invalid @enderror"
               placeholder="Computer Science and Engineering" required>
        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <textarea name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $branch->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $branch->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>