<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginField => Str::lower(trim($validated['login'])),
            'password' => $validated['password'],
            'account_status' => 'active',
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $audit->record('authentication.login_failed', 'authentication', metadata: ['reason' => 'invalid_credentials_or_inactive'], request: $request);

            throw ValidationException::withMessages([
                'login' => 'Kredensial tidak cocok atau akun belum aktif.',
            ]);
        }

        $request->session()->regenerate();
        $audit->record('authentication.login_succeeded', 'user', (int) Auth::id(), request: $request);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuditLogger $audit): RedirectResponse
    {
        $userId = Auth::id();
        $audit->record('authentication.logout', 'user', $userId === null ? null : (int) $userId, request: $request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
