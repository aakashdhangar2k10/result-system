@if (! empty($message))
    <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
        @if (! empty($existing))
            @can('subjectresults.edit')
                <a href="{{ route('results.edit', $existing) }}" class="alert-link ms-2">Edit that result</a>
            @endcan
        @endif
    </div>
@else
    @php
        $f = fn ($value) => \App\Services\ResultService::fmt($value);
        $hasInternal = $maxes['internal'] > 0;
        $hasExternal = $maxes['external'] > 0;
        $twoRows = $mode === 'marks' && $hasInternal;
        $rs = $twoRows ? 2 : 1;
    @endphp

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0" id="entryTable">
            <thead>
                <tr>
                    <th rowspan="{{ $rs }}" style="width:60px">Sr. No</th>
                    <th rowspan="{{ $rs }}">Roll No.</th>
                    <th rowspan="{{ $rs }}">Student Name</th>

                    @if ($mode === 'marks')
                        @if ($hasInternal)
                            <th colspan="3" class="text-center">
                                Internal Marks ({{ $f($maxes['internal']) }})
                                <div class="small fw-normal text-muted">best {{ \App\Services\ResultService::BEST_OF }} of 3 tests</div>
                            </th>
                        @endif
                        @if ($hasExternal)
                            <th rowspan="{{ $rs }}">External Marks ({{ $f($maxes['external']) }})</th>
                        @endif
                        <th rowspan="{{ $rs }}">Total Marks ({{ $f($maxes['internal'] + $maxes['external']) }})</th>
                        <th rowspan="{{ $rs }}">Grade</th>
                    @else
                        <th rowspan="{{ $rs }}" style="min-width:170px">Grade</th>
                    @endif

                    <th rowspan="{{ $rs }}">Grade Point</th>
                </tr>
                @if ($twoRows)
                    <tr>
                        @foreach (['T1', 'T2', 'T3'] as $label)
                            <th class="text-center small">{{ $label }} ({{ $f($testMax) }})</th>
                        @endforeach
                    </tr>
                @endif
            </thead>

            <tbody>
            @foreach ($students as $id => $student)
                @php
                    $p = $prefill->get($id);
                    $entryType = $student->pivot?->entry_type;
                @endphp
                <tr data-student="{{ $id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $student->rollno }}</td>
                    <td>
                        {{ $student->full_name }}
                        @if ($entryType && $entryType !== 'regular')
                            <span class="badge text-bg-warning">{{ \App\Models\Student\StudentList::TYPES[$entryType] ?? $entryType }}</span>
                        @endif
                    </td>

                    @if ($mode === 'marks')
                        @if ($hasInternal)
                            @foreach (['t1', 't2', 't3'] as $t)
                                @php $absent = $p && $p->{$t . '_absent'}; @endphp
                                <td>
                                    <input type="number" step="any" min="0" max="{{ $f($testMax) }}"
                                           class="form-control form-control-sm mark-input" data-field="{{ $t }}"
                                           name="rows[{{ $id }}][{{ $t }}]" value="{{ $p ? $f($p->$t) : '' }}"
                                           @disabled($absent)>
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input absent-check" id="a_{{ $id }}_{{ $t }}"
                                               name="rows[{{ $id }}][{{ $t }}_absent]" value="1" @checked($absent)>
                                        <label class="form-check-label small" for="a_{{ $id }}_{{ $t }}">Absent</label>
                                    </div>
                                </td>
                            @endforeach
                        @endif

                        @if ($hasExternal)
                            @php $absent = $p && $p->external_absent; @endphp
                            <td>
                                <input type="number" step="any" min="0" max="{{ $f($maxes['external']) }}"
                                       class="form-control form-control-sm mark-input" data-field="external"
                                       name="rows[{{ $id }}][external]" value="{{ $p ? $f($p->external) : '' }}"
                                       @disabled($absent)>
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input absent-check" id="a_{{ $id }}_external"
                                           name="rows[{{ $id }}][external_absent]" value="1" @checked($absent)>
                                    <label class="form-check-label small" for="a_{{ $id }}_external">Absent</label>
                                </div>
                            </td>
                        @endif

                        <td class="out-total text-center fw-bold">{{ $p ? $f($p->total) : '' }}</td>
                        <td class="out-grade text-center fw-bold">{{ $p?->grade }}</td>
                    @else
                        <td>
                            <select class="form-select form-select-sm mark-input" data-field="grade" name="rows[{{ $id }}][grade]">
                                <option value="">Select Grade</option>
                                @foreach ($letters as $letter)
                                    <option value="{{ $letter }}" @selected($p && strcasecmp((string) $p->grade, $letter) === 0)>{{ $letter }}</option>
                                @endforeach
                            </select>
                        </td>
                    @endif

                    <td class="out-point text-center fw-bold">{{ $p ? $p->grade_point : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="alert alert-warning mt-3 mb-0">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>* Please check all Grades before Submit a Result.</strong>
        Click <em>Calculate Grade</em> first; Submit is enabled only after the calculation succeeds.
    </div>
@endif