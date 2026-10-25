<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}" @selected(old('courseid', $student->courseid) == $course->courseid)>
                    {{ $course->fullname }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Session</label>
    <div class="col-sm-9">
        <select name="batchid" id="batchid" class="form-select @error('batchid') is-invalid @enderror" required>
            <option value="">Select Session</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->batchid }}" data-course="{{ $batch->courseid }}"
                    @selected(old('batchid', $student->batchid) == $batch->batchid)>
                    {{ $batch->label }}{{ $batch->branch ? ' – ' . $batch->branch->shortname : '' }}
                </option>
            @endforeach
        </select>
        @error('batchid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Roll No.</label>
    <div class="col-sm-9">
        <input type="text" name="rollno" value="{{ old('rollno', $student->rollno) }}"
               class="form-control @error('rollno') is-invalid @enderror" placeholder="Student Roll No." required>
        @error('rollno') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Enrollment No.</label>
    <div class="col-sm-9">
        <input type="text" name="enrollmentno" value="{{ old('enrollmentno', $student->enrollmentno) }}"
               class="form-control @error('enrollmentno') is-invalid @enderror" placeholder="Student Enrollment No." required>
        @error('enrollmentno') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">APAAR ID.</label>
    <div class="col-sm-9">
        <input type="text" name="apaarid" value="{{ old('apaarid', $student->apaarid) }}"
               class="form-control @error('apaarid') is-invalid @enderror" placeholder="Student APAAR ID.">
        @error('apaarid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">First Name</label>
    <div class="col-sm-9">
        <input type="text" name="firstname" value="{{ old('firstname', $student->firstname) }}"
               class="form-control @error('firstname') is-invalid @enderror" placeholder="Student First Name" required>
        @error('firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Last Name</label>
    <div class="col-sm-9">
        <input type="text" name="lastname" value="{{ old('lastname', $student->lastname) }}"
               class="form-control @error('lastname') is-invalid @enderror" placeholder="Student Last Name">
        @error('lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Father's Name</label>
    <div class="col-sm-9">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="father_label" class="form-select @error('father_label') is-invalid @enderror">
                    <option value="">Select Label</option>
                    @foreach (\App\Models\Student\Student::FATHER_LABELS as $label)
                        <option value="{{ $label }}" @selected(old('father_label', $student->father_label) == $label)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('father_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <select name="father_title" class="form-select @error('father_title') is-invalid @enderror">
                    <option value="">Select Title</option>
                    @foreach (\App\Models\Student\Student::FATHER_TITLES as $title)
                        <option value="{{ $title }}" @selected(old('father_title', $student->father_title) == $title)>{{ $title }}</option>
                    @endforeach
                </select>
                @error('father_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <input type="text" name="fathername" value="{{ old('fathername', $student->fathername) }}"
                       class="form-control @error('fathername') is-invalid @enderror"
                       placeholder="Student Father's Name" required>
                @error('fathername') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Mother's Name</label>
    <div class="col-sm-9">
        <div class="row g-2">
            <div class="col-md-4">
                <select name="mother_title" class="form-select @error('mother_title') is-invalid @enderror">
                    <option value="">Select Title</option>
                    @foreach (\App\Models\Student\Student::MOTHER_TITLES as $title)
                        <option value="{{ $title }}" @selected(old('mother_title', $student->mother_title) == $title)>{{ $title }}</option>
                    @endforeach
                </select>
                @error('mother_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8">
                <input type="text" name="mothername" value="{{ old('mothername', $student->mothername) }}"
                       class="form-control @error('mothername') is-invalid @enderror"
                       placeholder="Student Mother's Name" required>
                @error('mothername') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject</label>
    <div class="col-sm-9">
        <input type="text" name="subject" value="{{ old('subject', $student->subject) }}"
               class="form-control @error('subject') is-invalid @enderror" placeholder="Commerce (printed on the Ph.D. sheet)">
        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Optional. Used by Ph.D. sheets that print a Subject row.</div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Address</label>
    <div class="col-sm-9">
        <input type="text" name="address" value="{{ old('address', $student->address) }}"
               class="form-control @error('address') is-invalid @enderror" placeholder="Student Full Address">
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Mobile</label>
    <div class="col-sm-9">
        <input type="tel" name="mobile" value="{{ old('mobile', $student->mobile) }}"
               class="form-control @error('mobile') is-invalid @enderror" placeholder="Student Mobile No.">
        @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Image</label>
    <div class="col-sm-9">
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.gif,.png,image/*"
               class="form-control @error('image') is-invalid @enderror">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            Image File Extension: .jpg, .jpeg, .gif and .png (Max size: upto 2MB)
            @if ($student->image) — leave empty to keep the current photo. @endif
        </div>
        <img id="preview" src="{{ $student->image_url }}" alt="preview"
             class="img-thumbnail mt-2 {{ $student->image_url ? '' : 'd-none' }}" style="max-height:120px">
    </div>
</div>

<script>
    (function () {
        var course = document.getElementById('courseid');
        var batch = document.getElementById('batchid');
        var file = document.getElementById('image');
        var preview = document.getElementById('preview');

        // sessions of the selected course only
        function syncSessions() {
            Array.prototype.forEach.call(batch.options, function (o) {
                if (!o.value) return;
                var match = course.value !== '' && o.dataset.course === course.value;
                o.hidden = !match;
                o.disabled = !match;
            });

            var chosen = batch.options[batch.selectedIndex];
            if (chosen && chosen.disabled) batch.value = '';
        }

        course.addEventListener('change', syncSessions);
        syncSessions();

        // live image preview, and a quick size check before uploading
        file.addEventListener('change', function () {
            var picked = file.files[0];
            if (!picked) return;

            if (picked.size > 2 * 1024 * 1024) {
                alert('The image must not be larger than 2 MB.');
                file.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(picked);
        });
    })();
</script>