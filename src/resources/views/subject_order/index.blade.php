@extends('adminlte::page')

@section('title', 'Subject Order')

@section('content_header')
    <h3 class="mb-0">Subject Order</h3>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-primary card-outline">
        <form method="GET" action="{{ route('subject-orders.index') }}">
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Course</label>
                        <select name="courseid" class="form-select" required>
                            <option value="">Select Course</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->courseid }}" @selected(request('courseid') == $course->courseid)>
                                    {{ $course->fullname }} - {{ $course->duration_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Semester</label>
                        <select name="semid" class="form-select" required>
                            <option value="">Select Semester</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->semid }}" @selected(request('semid') == $term->semid)>
                                    {{ \App\Support\Term::base($term->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">Search</button>
                        <a href="{{ route('subject-orders.index') }}" class="btn btn-light border">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        @if (! $searched)
            <div class="card-body border-top text-center text-muted py-4">
                Select a course and a semester, then click Search to see the subject order.
            </div>
        @elseif ($subjects->isEmpty())
            <div class="card-body border-top text-center text-muted py-4">
                No subjects found for the selected course and semester.
            </div>
        @else
            <form id="orderForm" method="POST" action="{{ route('subject-orders.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="courseid" value="{{ request('courseid') }}">
                <input type="hidden" name="semid" value="{{ request('semid') }}">

                <div class="card-body p-0 table-responsive border-top">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px">Sr. No</th>
                                <th>Subject</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Subject Code</th>
                                <th>Credit</th>
                                <th>Type</th>
                                <th style="width:190px">
                                    Order
                                    @can('subjectorders.edit')
                                        <button type="button" id="editOrder" class="btn btn-sm btn-info text-white float-end"
                                                title="Edit order">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                </th>
                            </tr>
                        </thead>
                        <tbody id="orderBody">
                        @foreach ($subjects as $subject)
                            <tr>
                                <td class="sr">{{ $loop->iteration }}</td>
                                <td>
                                    {{ $subject->subjectname }}
                                    @unless ($subject->active)
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endunless
                                </td>
                                <td>{{ $subject->course?->fullname }} - {{ $subject->course?->duration_label }}</td>
                                <td>{{ \App\Support\Term::base($subject->term?->name) }}</td>
                                <td>{{ $subject->subjectcode }}</td>
                                <td>{{ (float) $subject->subjectcredit }}</td>
                                <td>{{ $subject->type_label }}</td>
                                <td>
                                    <input type="hidden" name="subject_ids[]" value="{{ $subject->subjectid }}">
                                    <strong class="order-no">{{ $loop->iteration }}</strong>
                                    <span class="order-tools d-none float-end">
                                        <button type="button" class="btn btn-sm btn-light border move-up" title="Move up">
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border move-down" title="Move down">
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-end d-none" id="orderActions">
                    <span class="text-muted me-3">Use the arrows to change the order, then save.</span>
                    <button type="submit" class="btn btn-primary">Save Order</button>
                    <button type="button" id="cancelOrder" class="btn btn-danger">Cancel</button>
                </div>
            </form>

            <script>
                (function () {
                    var body = document.getElementById('orderBody');
                    var editBtn = document.getElementById('editOrder');
                    var actions = document.getElementById('orderActions');
                    var cancelBtn = document.getElementById('cancelOrder');

                    function rows() { return Array.prototype.slice.call(body.querySelectorAll('tr')); }

                    // renumber Sr. No and Order after every move, and disable the arrows at the ends
                    function renumber() {
                        var all = rows();
                        all.forEach(function (row, i) {
                            row.querySelector('.sr').textContent = i + 1;
                            row.querySelector('.order-no').textContent = i + 1;
                            row.querySelector('.move-up').disabled = i === 0;
                            row.querySelector('.move-down').disabled = i === all.length - 1;
                        });
                    }

                    if (editBtn) {
                        editBtn.addEventListener('click', function () {
                            Array.prototype.forEach.call(document.querySelectorAll('.order-tools'), function (el) {
                                el.classList.remove('d-none');
                            });
                            actions.classList.remove('d-none');
                            editBtn.classList.add('d-none');
                            renumber();
                        });
                    }

                    body.addEventListener('click', function (event) {
                        var button = event.target.closest('.move-up, .move-down');
                        if (!button) return;

                        var row = button.closest('tr');
                        if (button.classList.contains('move-up') && row.previousElementSibling) {
                            body.insertBefore(row, row.previousElementSibling);
                        } else if (button.classList.contains('move-down') && row.nextElementSibling) {
                            body.insertBefore(row.nextElementSibling, row);
                        }
                        renumber();
                    });

                    // leaving edit mode simply reloads the saved order
                    cancelBtn.addEventListener('click', function () { window.location.reload(); });
                })();
            </script>
        @endif
    </div>
@stop