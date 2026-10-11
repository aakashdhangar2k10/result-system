<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" value="{{ old('fullname', $courseType->fullname) }}"
               class="form-control @error('fullname') is-invalid @enderror" required>
        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Short Name</label>
        <input type="text" name="shortname" value="{{ old('shortname', $courseType->shortname) }}"
               class="form-control @error('shortname') is-invalid @enderror" required>
        @error('shortname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $courseType->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
           @checked(old('active', $courseType->active ?? true))>
    <label class="form-check-label" for="active">Active</label>
</div>