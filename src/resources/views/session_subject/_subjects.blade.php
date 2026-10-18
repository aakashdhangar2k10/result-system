@if ($groups->isEmpty())
    <div class="text-muted">
        No subjects found for this course and semester. Add them under Subject first.
    </div>
@else
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="checkAll">
        <label class="form-check-label text-muted" for="checkAll">Select all</label>
    </div>

    @foreach ($groups as $group)
        <h6 class="fw-bold mt-3">{{ $group->first()->subjectType?->subjecttypename }} Subject</h6>

        @foreach ($group as $subject)
            <div class="form-check">
                <input class="form-check-input subject-check" type="checkbox" name="subject_ids[]"
                       id="subject_{{ $subject->subjectid }}" value="{{ $subject->subjectid }}"
                       @checked(in_array($subject->subjectid, $selected))>
                <label class="form-check-label" for="subject_{{ $subject->subjectid }}">
                    (<strong>{{ $subject->subjectcode }}</strong>) {{ $subject->subjectname }}
                    @unless ($subject->active)
                        <span class="badge text-bg-secondary">Inactive</span>
                    @endunless
                </label>
            </div>
        @endforeach
    @endforeach
@endif