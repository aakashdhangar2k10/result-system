@extends('adminlte::page')

@section('title', 'My Profile')

@section('content_header')
    <h3 class="mb-0">My Profile</h3>
@stop

@section('content')
    @include('partials.flash-error')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        {{-- who you are --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle shadow mb-3" style="width:110px;height:110px" alt="">
                    <h5 class="mb-0">{{ $user->name }}</h5>
                    <div class="text-muted">{{ $user->email }}</div>
                    <div class="mt-2">
                        @forelse ($user->roles as $role)
                            <span class="badge text-bg-primary">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted small">No role</span>
                        @endforelse
                    </div>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">
                        <strong>Groups</strong><br>
                        @forelse ($user->groups as $group)
                            <span class="badge text-bg-info">{{ $group->name }}</span>
                        @empty
                            <span class="text-muted">None</span>
                        @endforelse
                    </li>
                    <li class="list-group-item">
                        <strong>Departments</strong><br>
                        @if ($user->isAdmin() || $user->all_departments)
                            <span class="badge text-bg-success">All departments</span>
                        @else
                            @forelse ($user->departments as $department)
                                <span class="badge text-bg-secondary">{{ $department->fullname }}</span>
                            @empty
                                <span class="text-muted">None</span>
                            @endforelse
                        @endif
                    </li>
                </ul>
                <div class="card-footer small text-muted">
                    Your roles, groups and departments are set by an administrator.
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- name and email --}}
            <div class="card card-info card-outline">
                <div class="card-header"><h3 class="card-title mb-0">Account</h3></div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">This is your login. Changing it needs your current password.</div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Current Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="current_password" autocomplete="current-password"
                                       class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Only needed when you change the email.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-info text-white">Update</button>
                    </div>
                </form>
            </div>

            {{-- password --}}
            <div class="card card-warning card-outline">
                <div class="card-header"><h3 class="card-title mb-0">Change Password</h3></div>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Current Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="current_password" autocomplete="current-password"
                                       class="form-control @error('current_password', 'password') is-invalid @enderror" required>
                                @error('current_password', 'password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">New Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="password" autocomplete="new-password"
                                       class="form-control @error('password', 'password') is-invalid @enderror" required>
                                @error('password', 'password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">At least 8 characters with letters and numbers.</div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <label class="col-sm-3 col-form-label fw-bold text-sm-end">Confirm Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop