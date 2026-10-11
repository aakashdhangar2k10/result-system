<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $semester->name) }}"
               class="form-control @error('name') is-invalid @enderror" placeholder="First Semester" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Roman</label>
        <input type="text" name="roman" value="{{ old('roman', $semester->roman) }}"
               class="form-control @error('roman') is-invalid @enderror" placeholder="I" required>
        @error('roman') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Join Date</label>
        <input type="date" name="joindate" value="{{ old('joindate', $semester->joindate?->format('Y-m-d')) }}"
               class="form-control @error('joindate') is-invalid @enderror">
        @error('joindate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
           @checked(old('active', $semester->active ?? true))>
    <label class="form-check-label" for="active">Active</label>
</div>