<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->courseid }}"
                        data-terms="{{ $course->total_terms }}"
                        data-structure="{{ $course->structure_type }}"
                        @selected(old('courseid', $subject->courseid) == $course->courseid)>
                    {{ $course->fullname }} - {{ $course->duration_label }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        @selected(old('semid', $subject->semid) == $term->semid)>
                    {{ \App\Support\Term::label($term->name, 'semester') }}
                </option>
            @endforeach
        </select>
        @error('semid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject Type</label>
    <div class="col-sm-9">
        <select name="subjecttype" class="form-select @error('subjecttype') is-invalid @enderror" required>
            <option value="">Select Subject Type</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(old('subjecttype', $subject->subjecttype) == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('subjecttype') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject Name</label>
    <div class="col-sm-9">
        <input type="text" name="subjectname" value="{{ old('subjectname', $subject->subjectname) }}"
               class="form-control @error('subjectname') is-invalid @enderror" placeholder="Subject name" required>
        @error('subjectname') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject Credit</label>
    <div class="col-sm-9">
        <input type="number" step="0.5" min="0" max="30" name="subjectcredit"
               value="{{ old('subjectcredit', $subject->subjectcredit) }}"
               class="form-control @error('subjectcredit') is-invalid @enderror" placeholder="Subject credit like 4" required>
        @error('subjectcredit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Subject Code</label>
    <div class="col-sm-9">
        <input type="text" name="subjectcode" value="{{ old('subjectcode', $subject->subjectcode) }}"
               class="form-control @error('subjectcode') is-invalid @enderror" placeholder="Subject code" required>
        @error('subjectcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Max Internal Marks</label>
    <div class="col-sm-9">
        <input type="number" min="0" max="500" name="maxinternalmarks"
               value="{{ old('maxinternalmarks', $subject->maxinternalmarks) }}"
               class="form-control @error('maxinternalmarks') is-invalid @enderror" placeholder="Max internal marks like 30" required>
        @error('maxinternalmarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Max External Marks</label>
    <div class="col-sm-9">
        <input type="number" min="0" max="500" name="maxexternalmarks"
               value="{{ old('maxexternalmarks', $subject->maxexternalmarks) }}"
               class="form-control @error('maxexternalmarks') is-invalid @enderror" placeholder="Max external marks like 70" required>
        @error('maxexternalmarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $subject->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

<script>
    (function () {
        var course = document.getElementById('courseid');
        var term = document.getElementById('semid');
        var label = document.getElementById('term_label');

        function sync() {
            var opt = course.options[course.selectedIndex];
            var terms = opt && opt.dataset.terms ? parseInt(opt.dataset.terms, 10) : 0;
            var word = opt && opt.dataset.structure === 'year' ? 'Year' : 'Semester';

            label.textContent = word;

            // terms up to the course length, named "First Semester" or "First Year"
            Array.prototype.forEach.call(term.options, function (o) {
                if (!o.value) return;
                var within = terms > 0 && parseInt(o.dataset.number, 10) <= terms;
                o.hidden = !within;
                o.disabled = !within;
                o.textContent = (o.dataset.base ? o.dataset.base + ' ' : '') + word;
            });

            var chosen = term.options[term.selectedIndex];
            if (chosen && chosen.disabled) term.value = '';
        }

        course.addEventListener('change', sync);
        sync();
    })();
</script>