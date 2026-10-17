<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course Type</label>
    <div class="col-sm-9">
        <select name="coursetypeid" class="form-select @error('coursetypeid') is-invalid @enderror" required>
            <option value="">Select Course Type</option>
            @foreach ($courseTypes as $courseType)
                <option value="{{ $courseType->coursetypeid }}"
                    @selected(old('coursetypeid', $division->coursetypeid) == $courseType->coursetypeid)>
                    {{ $courseType->fullname }}
                </option>
            @endforeach
        </select>
        @error('coursetypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Division Type</label>
    <div class="col-sm-9">
        <select name="divtypeid" class="form-select @error('divtypeid') is-invalid @enderror" required>
            <option value="">Select Division Type</option>
            @foreach ($divisionTypes as $divisionType)
                <option value="{{ $divisionType->divtypeid }}"
                    @selected(old('divtypeid', $division->divtypeid) == $divisionType->divtypeid)>
                    {{ $divisionType->divtypename }}
                </option>
            @endforeach
        </select>
        @error('divtypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Division Name</label>
    <div class="col-sm-9">
        <input type="text" name="divisionname" value="{{ old('divisionname', $division->divisionname) }}"
               class="form-control @error('divisionname') is-invalid @enderror"
               placeholder="Division text name like First Division" required>
        @error('divisionname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Start Point</label>
    <div class="col-sm-9">
        <input type="number" step="0.01" min="0" max="100" name="startpoint"
               value="{{ old('startpoint', $division->startpoint) }}"
               class="form-control @error('startpoint') is-invalid @enderror" placeholder="8" required>
        @error('startpoint') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">End Point</label>
    <div class="col-sm-9">
        <input type="number" step="0.01" min="0" max="100" name="endpoint"
               value="{{ old('endpoint', $division->endpoint) }}"
               class="form-control @error('endpoint') is-invalid @enderror" placeholder="10" required>
        @error('endpoint') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $division->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>