<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email:rfc', 'max:150']]);
        $audit->record('authentication.password_reset_requested', 'authentication', request: $request);
        Password::sendResetLink($validated);

        return back()->with('status', 'Jika akun yang memenuhi syarat menggunakan email tersebut, tautan pemulihan akan dikirim.');
    }
}
