<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Course</label>
    <div class="col-sm-9">
        <select name="courseid" id="courseid" class="form-select @error('courseid') is-invalid @enderror" required>
            <option value="">Select Course</option>
            @foreach ($courses as $course)
                @php
                    $years = $course->structure_type === 'year'
                        ? $course->total_terms
                        : (int) ceil($course->total_terms / 2);
                @endphp
                <option value="{{ $course->courseid }}"
                        data-has-branch="{{ $course->has_branch ? 1 : 0 }}"
                        data-years="{{ $years }}"
                        @selected(old('courseid', $batch->courseid) == $course->courseid)>
                    {{ $course->shortname }} – {{ $course->fullname }}
                </option>
            @endforeach
        </select>
        @error('courseid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3" id="branch_row">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Branch</label>
    <div class="col-sm-9">
        <select name="branchid" id="branchid" class="form-select @error('branchid') is-invalid @enderror">
            <option value="">Select Branch</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->branchid }}" data-course="{{ $branch->courseid }}"
                    @selected(old('branchid', $batch->branchid) == $branch->branchid)>
                    {{ $branch->shortname }} – {{ $branch->fullname }}
                </option>
            @endforeach
        </select>
        @error('branchid') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Start Year</label>
    <div class="col-sm-9">
        <input type="number" name="startyear" id="startyear" min="1990" max="{{ date('Y') + 10 }}"
               value="{{ old('startyear', $batch->startyear) }}"
               class="form-control @error('startyear') is-invalid @enderror" placeholder="2024" required>
        @error('startyear') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">End Year</label>
    <div class="col-sm-9">
        <input type="number" name="endyear" id="endyear" min="1990" max="{{ date('Y') + 15 }}"
               value="{{ old('endyear', $batch->endyear) }}"
               class="form-control @error('endyear') is-invalid @enderror" placeholder="2028" required>
        <div class="form-text">Filled in automatically from the course duration; you can change it.</div>
        @error('endyear') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-9">
        <textarea name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $batch->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $batch->active ?? true))>
            <label class="form-check-label" for="active">Active</label>
        </div>
    </div>
</div>

<script>
    (function () {
        var course = document.getElementById('courseid');
        var branchRow = document.getElementById('branch_row');
        var branch = document.getElementById('branchid');
        var start = document.getElementById('startyear');
        var end = document.getElementById('endyear');

        // an end year that is already filled (edit page, or after a validation error) is left alone
        var endTouched = end.value !== '';

        function suggestEnd() {
            var opt = course.options[course.selectedIndex];
            if (endTouched || !opt || !opt.dataset.years || !start.value) return;
            end.value = parseInt(start.value, 10) + parseInt(opt.dataset.years, 10);
        }

        function sync() {
            var opt = course.options[course.selectedIndex];
            var hasBranch = !!opt && opt.dataset.hasBranch === '1';

            // show the branch field only for courses that have branches
            branchRow.style.display = hasBranch ? '' : 'none';
            branch.disabled = !hasBranch;

            // show only the branches of the selected course
            Array.prototype.forEach.call(branch.options, function (o) {
                if (!o.value) return;
                var match = !!opt && o.dataset.course === opt.value;
                o.hidden = !match;
                o.disabled = !match;
            });

            var chosen = branch.options[branch.selectedIndex];
            if (!hasBranch || (chosen && chosen.disabled)) branch.value = '';

            suggestEnd();
        }

        end.addEventListener('input', function () { endTouched = true; });
        start.addEventListener('input', suggestEnd);
        course.addEventListener('change', function () { endTouched = false; sync(); });

        sync();
    })();
</script>