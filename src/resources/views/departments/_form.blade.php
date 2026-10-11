<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" value="{{ old('fullname', $department->fullname) }}"
               class="form-control @error('fullname') is-invalid @enderror" required>
        @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Short Name</label>
        <input type="text" name="shortname" value="{{ old('shortname', $department->shortname) }}"
               class="form-control @error('shortname') is-invalid @enderror" required>
        @error('shortname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" rows="3"
              class="form-control @error('address') is-invalid @enderror">{{ old('address', $department->address) }}</textarea>
    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-5 mb-3">
        <label class="form-label">Affiliation Name</label>
        <input type="text" name="affilationname" value="{{ old('affilationname', $department->affilationname) }}"
               class="form-control @error('affilationname') is-invalid @enderror">
        @error('affilationname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Affiliation Short Name</label>
        <input type="text" name="affilationshortname" value="{{ old('affilationshortname', $department->affilationshortname) }}"
               class="form-control @error('affilationshortname') is-invalid @enderror">
        @error('affilationshortname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Affiliation Year</label>
        <input type="number" name="affilationyear" value="{{ old('affilationyear', $department->affilationyear) }}"
               class="form-control @error('affilationyear') is-invalid @enderror" placeholder="2020">
        @error('affilationyear') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Join Date</label>
        <input type="date" name="joindate" value="{{ old('joindate', $department->joindate?->format('Y-m-d')) }}"
               class="form-control @error('joindate') is-invalid @enderror">
        @error('joindate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Logo (jpg, png, webp, max 2 MB)</label>
        <input type="file" name="logo" accept="image/*"
               class="form-control @error('logo') is-invalid @enderror">
        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($department->logo)
            <img src="{{ $department->logo_url }}" alt="Logo" class="mt-2" style="height:60px">
        @endif
    </div>
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
           @checked(old('active', $department->active ?? true))>
    <label class="form-check-label" for="active">Active</label>
</div>