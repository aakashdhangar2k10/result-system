<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject Type Name</label>
    <div class="col-sm-9">
        <input type="text" name="subjecttypename" value="{{ old('subjecttypename', $subjectType->subjecttypename) }}"
               class="form-control @error('subjecttypename') is-invalid @enderror"
               placeholder="Comprehensive Viva" required>
        @error('subjecttypename') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject form asks for</label>
    <div class="col-sm-9">
        <div class="form-check">
            <input type="hidden" name="has_internal" value="0">
            <input class="form-check-input" type="checkbox" name="has_internal" id="has_internal" value="1"
                   @checked(old('has_internal', $subjectType->has_internal))>
            <label class="form-check-label" for="has_internal">Max Internal Marks</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="has_external" value="0">
            <input class="form-check-input" type="checkbox" name="has_external" id="has_external" value="1"
                   @checked(old('has_external', $subjectType->has_external))>
            <label class="form-check-label" for="has_external">Max External Marks</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="has_grade" value="0">
            <input class="form-check-input" type="checkbox" name="has_grade" id="has_grade" value="1"
                   @checked(old('has_grade', $subjectType->has_grade))>
            <label class="form-check-label" for="has_grade">"Add Grade for this subject" tick</label>
        </div>
        @error('has_internal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="is_optional" value="0">
            <input class="form-check-input" type="checkbox" name="is_optional" id="is_optional" value="1"
                   @checked(old('is_optional', $subjectType->is_optional))>
            <label class="form-check-label" for="is_optional">
                Optional subject: only the students who choose it take it
                (chosen under Student → Optional Subject Student)
            </label>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $subjectType->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>