<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course Type</label>
    <div class="col-sm-9">
        <select name="coursetypeid" class="form-select @error('coursetypeid') is-invalid @enderror" required>
            <option value="">Select Course Type</option>
            @foreach ($courseTypes as $courseType)
                <option value="{{ $courseType->coursetypeid }}"
                    @selected(old('coursetypeid', $grade->coursetypeid) == $courseType->coursetypeid)>
                    {{ $courseType->fullname }}
                </option>
            @endforeach
        </select>
        @error('coursetypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Grade Type</label>
    <div class="col-sm-9">
        <select name="gradetypeid" class="form-select @error('gradetypeid') is-invalid @enderror" required>
            <option value="">Select Grade Type</option>
            @foreach ($gradeTypes as $gradeType)
                <option value="{{ $gradeType->coursetypeid }}"
                    @selected(old('gradetypeid', $grade->gradetypeid) == $gradeType->coursetypeid)>
                    {{ $gradeType->coursetypename }}
                </option>
            @endforeach
        </select>
        @error('gradetypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">First Attempt Grade</label>
    <div class="col-sm-9">
        <input type="text" name="firstattemptgrade" value="{{ old('firstattemptgrade', $grade->firstattemptgrade) }}"
               class="form-control @error('firstattemptgrade') is-invalid @enderror" placeholder="O" required>
        @error('firstattemptgrade') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">First Attempt Credit</label>
    <div class="col-sm-9">
        <input type="number" name="firstattemptcredit" value="{{ old('firstattemptcredit', $grade->firstattemptcredit) }}"
               class="form-control @error('firstattemptcredit') is-invalid @enderror" placeholder="10" min="0" required>
        @error('firstattemptcredit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Second Attempt Grade</label>
    <div class="col-sm-9">
        <input type="text" name="secondattemptgrade" value="{{ old('secondattemptgrade', $grade->secondattemptgrade) }}"
               class="form-control @error('secondattemptgrade') is-invalid @enderror" placeholder="O" required>
        @error('secondattemptgrade') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Second Attempt Credit</label>
    <div class="col-sm-9">
        <input type="number" name="secondattemptcredit" value="{{ old('secondattemptcredit', $grade->secondattemptcredit) }}"
               class="form-control @error('secondattemptcredit') is-invalid @enderror" placeholder="9" min="0" required>
        @error('secondattemptcredit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Start Score</label>
    <div class="col-sm-9">
        <input type="number" name="startscore" value="{{ old('startscore', $grade->startscore) }}"
               class="form-control @error('startscore') is-invalid @enderror" placeholder="90" min="0" max="100" required>
        @error('startscore') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">End Score</label>
    <div class="col-sm-9">
        <input type="number" name="endscore" value="{{ old('endscore', $grade->endscore) }}"
               class="form-control @error('endscore') is-invalid @enderror" placeholder="100" min="0" max="100" required>
        @error('endscore') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <input type="text" name="description" value="{{ old('description', $grade->description) }}"
               class="form-control @error('description') is-invalid @enderror" placeholder="Outstanding">
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $grade->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

<div class="alert alert-warning mb-0">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>* For Absent Grade use 'Ab'.</strong>
</div>