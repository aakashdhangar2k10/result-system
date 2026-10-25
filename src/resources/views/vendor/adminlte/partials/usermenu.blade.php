@php $menuUser = auth()->user(); @endphp

@if ($menuUser)
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ $menuUser->avatar_url }}" class="user-image rounded-circle shadow" style="object-fit:cover" alt="{{ $menuUser->name }}">
            <span class="d-none d-md-inline">{{ $menuUser->name }}</span>
        </a>

        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <li class="user-header text-bg-primary">
                <img src="{{ $menuUser->avatar_url }}" class="rounded-circle shadow" style="object-fit:cover" alt="">
                <p>
                    {{ $menuUser->name }}
                    <small>{{ $menuUser->email }}</small>
                    <small>{{ $menuUser->getRoleNames()->implode(', ') }}</small>
                </p>
            </li>

            <li class="user-footer">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person"></i> My Profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="float-end">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Sign out
                    </button>
                </form>
            </li>
        </ul>
    </li>
@endif