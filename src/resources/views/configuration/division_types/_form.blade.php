<div class="mb-3">
    <label class="form-label">Division Type Name</label>
    <input type="text" name="divtypename" value="{{ old('divtypename', $divisionType->divtypename) }}"
           class="form-control @error('divtypename') is-invalid @enderror" required>
    @error('divtypename') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $divisionType->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
           @checked(old('active', $divisionType->active ?? true))>
    <label class="form-check-label" for="active">Active</label>
</div>