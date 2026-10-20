@php $checked = array_map('intval', $checked); @endphp

<div class="d-flex justify-content-between align-items-center mb-2">
    <input type="search" id="matrixSearch" class="form-control form-control-sm w-auto" placeholder="Search module">
    @unless ($locked)
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="matrixAll">
            <label class="form-check-label fw-bold" for="matrixAll">Select everything</label>
        </div>
    @endunless
</div>

<div class="table-responsive border" style="max-height:540px">
    <table class="table table-sm table-striped align-middle mb-0" id="matrixTable">
        <thead class="sticky-top bg-body">
            <tr>
                <th>Module</th>
                @foreach ($matrix['columns'] as $column)
                    <th class="text-center">
                        <div>{{ $column['label'] }}</div>
                        @unless ($locked)
                            <input type="checkbox" class="form-check-input matrix-col" data-col="{{ $column['key'] }}" title="Select the whole column">
                        @endunless
                    </th>
                @endforeach
                <th class="text-center">All</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($matrix['rows'] as $row)
            <tr data-search="{{ mb_strtolower($row['label']) }}">
                <td>{{ $row['label'] }}</td>
                @foreach ($matrix['columns'] as $column)
                    @php $permission = $row['cells'][$column['key']] ?? null; @endphp
                    <td class="text-center">
                        @if ($permission)
                            <input type="checkbox" class="form-check-input matrix-cell" name="permissions[]"
                                   value="{{ $permission->id }}" data-col="{{ $column['key'] }}" title="{{ $permission->name }}"
                                   @checked($locked || in_array($permission->id, $checked, true))
                                   @disabled($locked || ! in_array($permission->name, $grantable, true))>
                        @else
                            <span class="text-muted">–</span>
                        @endif
                    </td>
                @endforeach
                <td class="text-center">
                    @unless ($locked)
                        <input type="checkbox" class="form-check-input matrix-row" title="Select the whole row">
                    @endunless
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if ($locked)
    <div class="form-text">The administrator role always has every permission.</div>
@endif

<script>
    (function () {
        var table = document.getElementById('matrixTable');
        var cells = function (scope, extra) {
            return Array.prototype.filter.call(
                scope.querySelectorAll('.matrix-cell' + (extra || '')),
                function (box) { return !box.disabled; }
            );
        };

        // search by module name
        document.getElementById('matrixSearch').addEventListener('input', function (event) {
            var q = event.target.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.dataset.search.indexOf(q) !== -1 ? '' : 'none';
            });
        });

        table.addEventListener('change', function (event) {
            var box = event.target;

            if (box.classList.contains('matrix-col')) {
                cells(table, '[data-col="' + box.dataset.col + '"]').forEach(function (cell) { cell.checked = box.checked; });
            } else if (box.classList.contains('matrix-row')) {
                cells(box.closest('tr')).forEach(function (cell) { cell.checked = box.checked; });
            }
        });

        var all = document.getElementById('matrixAll');
        if (all) {
            all.addEventListener('change', function () {
                cells(table).forEach(function (cell) { cell.checked = all.checked; });
                table.querySelectorAll('.matrix-col, .matrix-row').forEach(function (box) { box.checked = all.checked; });
            });
        }
    })();
</script>