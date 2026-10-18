<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}"
                        data-terms="{{ $course->total_terms }}"
                        data-structure="{{ $course->structure_type }}"
                        @selected(old('courseid', $courseSemester->courseid) == $course->courseid)>
                    {{ $course->fullname }} - {{ $course->duration_label }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
    <div class="col-sm-9">
        <select name="batchid" id="batchid" class="form-select @error('batchid') is-invalid @enderror">
            <option value="">Select Session</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                    @selected(old('batchid', $courseSemester->batchid) == $batch->batchid)>
                    {{ $batch->label }}{{ $batch->branch ? ' – ' . $batch->branch->shortname : '' }}
                </option>
            @endforeach
        </select>
        @error('batchid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end" id="term_label">Semester</label>
    <div class="col-sm-9">
        <select name="semid" id="semid" class="form-select @error('semid') is-invalid @enderror" required>
            <option value="">Select</option>
            @foreach ($terms as $term)
                <option value="{{ $term->semid }}"
                        data-number="{{ \App\Support\Term::number($term->roman) }}"
                        data-base="{{ \App\Support\Term::base($term->name) }}"
                        @selected(old('semid', $courseSemester->semid) == $term->semid)>
                    {{ \App\Support\Term::label($term->name, 'semester') }}
                </option>
            @endforeach
        </select>
        @error('semid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Semester Month</label>
    <div class="col-sm-9">
        <input type="date" name="semestermonth"
               value="{{ old('semestermonth', $courseSemester->semestermonth?->format('Y-m-d')) }}"
               class="form-control @error('semestermonth') is-invalid @enderror" required>
        @error('semestermonth') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Valid Credit</label>
    <div class="col-sm-9">
        <input type="number" name="validcredit" min="0" max="200"
               value="{{ old('validcredit', $courseSemester->validcredit) }}"
               class="form-control @error('validcredit') is-invalid @enderror" placeholder="Valid credit like 12" required>
        @error('validcredit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <textarea name="description" rows="3" placeholder="Description"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $courseSemester->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $courseSemester->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

@include('semester._cascade')