@extends('layouts.custom-auth')

@section('title', 'Registrasi')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">Nama Lengkap</label>
        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus placeholder="Budi Santoso">
    </div>

    <div class="form-group">
        <label for="phone" class="form-label">Nomor WhatsApp</label>
        <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" required placeholder="0812xxxx">
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email Kantor</label>
        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="nama@arwanacitra.com">
    </div>

    <div class="form-group">
        <label for="department_id" class="form-label">Departemen</label>
        <select name="department_id" id="department_id" class="form-control" required>
            <option value="" disabled selected>-- Pilih Departemen --</option>
            
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
            
        </select>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" class="form-control" name="password" required>
    </div>

    <div class="form-group">
        <label for="password-confirm" class="form-label">Konfirmasi Password</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn-primary">
        Daftar Akun Baru
    </button>
</form>

<div class="auth-footer">
    <p>Sudah punya akun? <a href="{{ route('login') }}">Login disini</a></p>
</div>
@endsection