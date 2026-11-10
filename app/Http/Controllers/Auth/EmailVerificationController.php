<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function createResend(): View
    {
        return view('auth.resend-verification');
    }

    public function notice(): View|RedirectResponse
    {
        if (request()->user()->hasVerifiedEmail() && request()->user()->account_status === 'active') {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash, AuditLogger $audit): RedirectResponse
    {
        $user = User::query()->findOrFail($id);
        abort_unless($user->email !== null && hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        DB::transaction(function () use ($user, $audit, $request): void {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->hasVerifiedEmail()) {
                return;
            }

            $locked->markEmailAsVerified();
            event(new Verified($locked));
            $audit->record('account.email_verified', 'user', (int) $locked->getKey(), request: $request);
        });

        if ($request->user()?->is($user)) {
            return redirect()->route('verification.notice')->with('status', 'Email berhasil diverifikasi. Akun Anda masih menunggu aktivasi administrator.');
        }

        return redirect()->route('login')->with('status', 'Email berhasil diverifikasi. Akun menunggu aktivasi administrator sebelum dapat digunakan.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Tautan verifikasi dikirim ulang jika email akun dapat menerima pesan.');
    }

    public function requestResend(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $email = Str::lower(trim($validated['email']));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('account_status', ['pending', 'active'])
            ->first();

        if ($user !== null && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $audit->record('account.email_verification_resent_public', 'user', (int) $user->getKey(), request: $request);
        }

        return back()->with('status', 'Jika email cocok dengan akun yang perlu diverifikasi, tautan akan dikirim. Periksa kotak masuk dan folder spam.');
    }
}
