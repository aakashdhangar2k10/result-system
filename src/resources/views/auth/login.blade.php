<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Result System</title>
    @vite(['resources/css/adminlte.css', 'resources/js/adminlte.js'])
</head>
<body class="login-page bg-body-secondary">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h1 class="mb-0">Result System</h1>
        </div>
        <div class="card-body login-card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="Email" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <input type="password" name="password" class="form-control"
                           placeholder="Password" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>