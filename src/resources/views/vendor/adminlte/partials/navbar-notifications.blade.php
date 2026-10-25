@php $bar = \App\Support\DepartmentAccess::switcher(); @endphp

@if ($bar['show'])
    <li class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Department">
            <i class="bi bi-building"></i>
            <span class="d-none d-md-inline ms-1">{{ $bar['label'] }}</span>
        </a>

        <form method="POST" action="{{ route('department-context.update') }}" class="dropdown-menu dropdown-menu-end">
            @csrf
            <h6 class="dropdown-header">Work in department</h6>

            <button type="submit" name="deptid" value="" class="dropdown-item {{ $bar['selected'] === null ? 'active' : '' }}">
                {{ \App\Support\DepartmentAccess::hasAll() ? 'All departments' : 'All my departments' }}
            </button>

            <div class="dropdown-divider"></div>

            @foreach ($bar['items'] as $item)
                <button type="submit" name="deptid" value="{{ $item->deptid }}"
                        class="dropdown-item {{ (int) $bar['selected'] === (int) $item->deptid ? 'active' : '' }}">
                    {{ $item->fullname }}
                </button>
            @endforeach
        </form>
    </li>
@endif