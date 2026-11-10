<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use LogicException;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        try {
            $status = DB::transaction(fn (): string => Password::reset($validated, function ($user, string $password) use ($audit, $request): void {
                if ($user->account_status !== 'active' || ! $user->hasVerifiedEmail()) {
                    throw new LogicException('Password reset is unavailable for this account.');
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $audit->record('authentication.password_reset_completed', 'user', (int) $user->getKey(), request: $request);
            }));
        } catch (LogicException) {
            $status = Password::INVALID_USER;
        }

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk.');
        }

        $audit->record('authentication.password_reset_failed', 'authentication', metadata: ['reason' => 'invalid_or_expired_token'], request: $request);

        return back()->withInput($request->only('email'))->withErrors(['email' => 'Tautan pemulihan tidak valid atau sudah kedaluwarsa.']);
    }
}
