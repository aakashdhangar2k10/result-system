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
        <select name="subjecttypeid" id="subjecttypeid" class="form-select @error('subjecttypeid') is-invalid @enderror" required>
            <option value="">Select Subject Type</option>
            @foreach ($subjectTypes as $subjectType)
                <option value="{{ $subjectType->subjecttypeid }}"
                        data-internal="{{ $subjectType->has_internal ? 1 : 0 }}"
                        data-external="{{ $subjectType->has_external ? 1 : 0 }}"
                        data-grade="{{ $subjectType->has_grade ? 1 : 0 }}"
                        @selected(old('subjecttypeid', $subject->subjecttypeid) == $subjectType->subjecttypeid)>
                    {{ $subjectType->subjecttypename }}
                </option>
            @endforeach
        </select>
        @error('subjecttypeid') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <input type="number" name="subjectcredit" step="0.5" min="0" max="30"
               value="{{ old('subjectcredit', isset($subject->subjectcredit) ? (float) $subject->subjectcredit : '') }}"
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

{{-- shown or hidden by the selected subject type --}}
<div class="row mb-3" id="row_internal">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Max Internal Marks</label>
    <div class="col-sm-9">
        <input type="number" name="maxinternalmarks" min="0" max="1000"
               value="{{ old('maxinternalmarks', $subject->maxinternalmarks) }}"
               class="form-control @error('maxinternalmarks') is-invalid @enderror" placeholder="30" required>
        @error('maxinternalmarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3" id="row_external">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Max External Marks</label>
    <div class="col-sm-9">
        <input type="number" name="maxexternalmarks" min="0" max="1000"
               value="{{ old('maxexternalmarks', $subject->maxexternalmarks) }}"
               class="form-control @error('maxexternalmarks') is-invalid @enderror" placeholder="70" required>
        @error('maxexternalmarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3" id="row_grade">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="add_grade" value="0">
            <input class="form-check-input" type="checkbox" name="add_grade" id="add_grade" value="1"
                   @checked(old('add_grade', $subject->add_grade))>
            <label class="form-check-label" for="add_grade">Add Grade for this subject</label>
        </div>
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
        var type = document.getElementById('subjecttypeid');
        var rowInternal = document.getElementById('row_internal');
        var rowExternal = document.getElementById('row_external');
        var rowGrade = document.getElementById('row_grade');

        // semester / year follows the course
        function syncTerm() {
            var opt = course.options[course.selectedIndex];
            var terms = opt && opt.dataset.terms ? parseInt(opt.dataset.terms, 10) : 0;
            var word = opt && opt.dataset.structure === 'year' ? 'Year' : 'Semester';

            label.textContent = word;

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

        // a hidden row is also disabled, so its inputs are not submitted
        function toggle(row, on) {
            row.style.display = on ? '' : 'none';
            Array.prototype.forEach.call(row.querySelectorAll('input'), function (input) {
                input.disabled = !on;
            });
        }

        // marks and grade fields follow the subject type
        function syncType() {
            var opt = type.options[type.selectedIndex];
            var chosen = !!opt && opt.value !== '';

            toggle(rowInternal, chosen && opt.dataset.internal === '1');
            toggle(rowExternal, chosen && opt.dataset.external === '1');
            toggle(rowGrade, chosen && opt.dataset.grade === '1');
        }

        course.addEventListener('change', syncTerm);
        type.addEventListener('change', syncType);
        syncTerm();
        syncType();
    })();
</script>