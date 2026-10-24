@php $bar = \App\Support\DepartmentAccess::switcher(); @endphp

@if ($bar['show'])
    <div class="d-flex justify-content-end mb-2">
        <form method="POST" action="{{ route('department-context.update') }}" class="dropdown">
            @csrf
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-building"></i> Department: <strong>{{ $bar['label'] }}</strong>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button type="submit" name="deptid" value="" class="dropdown-item {{ $bar['selected'] === null ? 'active' : '' }}">
                        {{ \App\Support\DepartmentAccess::hasAll() ? 'All departments' : 'All my departments' }}
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach ($bar['items'] as $item)
                    <li>
                        <button type="submit" name="deptid" value="{{ $item->deptid }}"
                                class="dropdown-item {{ (int) $bar['selected'] === (int) $item->deptid ? 'active' : '' }}">
                            {{ $item->fullname }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </form>
    </div>
@endif