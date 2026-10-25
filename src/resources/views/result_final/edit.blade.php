@extends('adminlte::page')

@section('title', 'Final Result Points')

@section('content_header')
    <h3 class="mb-0">Final Result Points</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    @php
        $word = $course->structure_type === 'year' ? 'Year' : 'Semester';
        $total = (int) $course->total_terms;
        $initial = array_values((array) old('finals', $rows));
    @endphp

    <div class="card card-primary card-outline">
        <form action="{{ route('result-finals.update', $course) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <h5 class="mb-1">{{ $course->fullname }}</h5>
                <p class="text-muted">{{ $total }} {{ strtolower($word) }}{{ $total == 1 ? '' : 's' }}</p>

                <div class="alert alert-light border small">
                    <strong>Examples</strong><br>
                    Dual degree, 10 {{ strtolower($word) }}s: <em>{{ $word }} 1 to 6 (B.B.A.(FT))</em> and <em>{{ $word }} 7 to 10 (M.B.A.(FT))</em>.<br>
                    Exit after {{ strtolower($word) }} 6 of an 8-{{ strtolower($word) }} course: <em>{{ $word }} 1 to 6</em> and <em>{{ $word }} 1 to 8</em>.<br>
                    Leave the list empty for one final result after the last {{ strtolower($word) }}.
                </div>

                <div class="row g-2 fw-bold small mb-1">
                    <div class="col-md-2">From {{ strtolower($word) }}</div>
                    <div class="col-md-2">To {{ strtolower($word) }} (final)</div>
                    <div class="col-md-6">Degree / certificate name (optional)</div>
                </div>

                <div id="finalList"></div>

                <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="addFinal">
                    <i class="bi bi-plus-lg"></i> Add a final result point
                </button>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('result-finals.index') }}" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var total = {{ $total }};
            var list = document.getElementById('finalList');
            var rows = @json($initial);
            var counter = 0;

            function select(name, selected) {
                var el = document.createElement('select');
                el.name = name;
                el.className = 'form-select form-select-sm';

                for (var i = 1; i <= total; i++) {
                    var o = document.createElement('option');
                    o.value = i;
                    o.textContent = i;
                    if (String(i) === String(selected)) o.selected = true;
                    el.appendChild(o);
                }

                return el;
            }

            function addRow(row) {
                if (list.children.length >= 6) return;

                var index = counter++;
                var group = document.createElement('div');
                group.className = 'row g-2 mb-2 align-items-center';

                var from = document.createElement('div');
                from.className = 'col-md-2';
                from.appendChild(select('finals[' + index + '][from_term]', row ? row.from_term : 1));

                var to = document.createElement('div');
                to.className = 'col-md-2';
                to.appendChild(select('finals[' + index + '][to_term]', row ? row.to_term : total));

                var label = document.createElement('div');
                label.className = 'col-md-6';
                var input = document.createElement('input');
                input.type = 'text';
                input.name = 'finals[' + index + '][degree_label]';
                input.maxLength = 100;
                input.value = row && row.degree_label ? row.degree_label : '';
                input.placeholder = 'B.B.A.(FT)';
                input.className = 'form-control form-control-sm';
                label.appendChild(input);

                var action = document.createElement('div');
                action.className = 'col-md-1';
                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-sm btn-outline-danger';
                remove.innerHTML = '<i class="bi bi-x-lg"></i>';
                remove.title = 'Remove';
                remove.addEventListener('click', function () { group.remove(); });
                action.appendChild(remove);

                group.appendChild(from);
                group.appendChild(to);
                group.appendChild(label);
                group.appendChild(action);
                list.appendChild(group);
            }

            rows.forEach(addRow);
            document.getElementById('addFinal').addEventListener('click', function () { addRow(null); });
        })();
    </script>
@stop