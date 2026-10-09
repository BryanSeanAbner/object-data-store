<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Satu halaman, dua tampilan:
     * - tanpa token  -> form minta link reset (isi email)
     * - dengan token -> form password baru (dari link di email)
     */
    public function show(Request $request, ?string $token = null): View
    {
        return view('auth.forgot-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Langkah 1: cek email di tabel users, buat token di tabel
     * password_reset_tokens, lalu kirim link lewat email.
     */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Terlalu sering meminta link. Tunggu 1 menit lalu coba lagi.']);
        }

        // Pesan sama untuk email terdaftar maupun tidak,
        // supaya orang lain tidak bisa menebak email mana yang punya akun.
        return back()->with(
            'status',
            'Jika email terdaftar, link reset password sudah dikirim. Cek kotak masuk atau folder spam.'
        );
    }

    /**
     * Langkah 2: verifikasi token + email, lalu simpan password baru.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('articles.index')
                ->with('status', 'Password berhasil diubah.');
        }

        $pesan = [
            Password::INVALID_TOKEN => 'Link reset tidak valid atau sudah kedaluwarsa. Minta link baru.',
            Password::INVALID_USER => 'Email tidak cocok dengan link reset ini.',
        ][$status] ?? 'Password gagal diubah. Coba minta link baru.';

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $pesan]);
    }
}
