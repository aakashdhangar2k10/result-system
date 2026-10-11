<div class="mb-3">
    <label class="form-label">Grade Type Name</label>
    <input type="text" name="coursetypename" value="{{ old('coursetypename', $gradeType->coursetypename) }}"
           class="form-control @error('coursetypename') is-invalid @enderror" required>
    @error('coursetypename') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
           @checked(old('active', $gradeType->active ?? true))>
    <label class="form-check-label" for="active">Active</label>
</div>