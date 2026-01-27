@extends('layouts.custom-auth')

@section('title', 'Login')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@arwanacitra.com">
        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" class="form-control" name="password" required placeholder="••••••••">
        @error('password')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn-primary">
        Masuk Aplikasi
    </button>
</form>

<div class="auth-footer">
    <p>Belum punya akun? <a href="{{ route('register') }}">Daftar disini</a></p>
</div>
@endsection