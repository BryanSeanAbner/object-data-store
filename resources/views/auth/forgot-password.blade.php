@extends('layouts.app')

@section('title', $token ? 'Buat Password Baru — Nusantara TV' : 'Lupa Password — Nusantara TV')

@section('content')
    <main class="mx-auto flex max-w-[1180px] justify-center px-3 py-10 sm:px-4 sm:py-16">
        <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            @if ($token)
                {{-- ===== Tampilan 2: password baru (dibuka dari link email) ===== --}}
                <h1 class="text-xl font-black tracking-tight text-blue-900">Buat password baru</h1>
                <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600">
                    Masukkan password baru untuk akunmu. Minimal 8 karakter.
                </p>
            @else
                {{-- ===== Tampilan 1: minta link reset ===== --}}
                <h1 class="text-xl font-black tracking-tight text-blue-900">Lupa password?</h1>
                <p class="mt-1.5 text-[13px] leading-relaxed text-slate-600">
                    Masukkan email akunmu. Kami kirim link untuk membuat password baru.
                </p>
            @endif

            {{-- Pesan sukses --}}
            @if (session('status'))
                <div role="status"
                    class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-[13px] font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pesan error umum --}}
            @if ($errors->any())
                <div role="alert"
                    class="mt-5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-[13px] text-red-800">
                    <ul class="space-y-0.5 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($token)
                <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="mb-1.5 block text-[13px] font-bold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" required autocomplete="email"
                            value="{{ old('email', $email) }}" readonly
                            class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-[13px] font-bold text-slate-700">Password baru</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                            autofocus
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-[13px] font-bold text-slate-700">Ulangi
                            password baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-blue-800 px-4 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">
                        Simpan password baru
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-[13px] font-bold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" required autocomplete="email" autofocus
                            value="{{ old('email') }}" placeholder="nama@email.com"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-blue-800 px-4 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">
                        Kirim link reset
                    </button>
                </form>
            @endif

            <p class="mt-6 text-center text-[12px] text-slate-500">
                <a href="{{ route('articles.index') }}" class="font-bold text-blue-800 hover:underline">
                    Kembali ke artikel
                </a>
            </p>
        </section>
    </main>
@endsection