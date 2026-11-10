<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->merge([
            'username' => mb_strtolower(trim((string) $request->input('username'))),
            'email' => $request->filled('email') ? mb_strtolower(trim((string) $request->input('email'))) : null,
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash:ascii', 'min:3', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = DB::transaction(function () use ($validated, $audit, $request): User {
            $residentRole = Role::query()->where('name', 'Resident')->firstOrFail();

            $user = new User;
            $user->name = 'account-'.$validated['username'];
            $user->username = $validated['username'];
            $user->email = $validated['email'];
            $user->password = $validated['password'];
            $user->account_status = 'pending';
            $user->save();
            $user->roles()->attach($residentRole->getKey());
            $audit->record('account.registration_submitted', 'user', (int) $user->getKey(), ['status' => 'pending'], $request);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('status', 'Pendaftaran diterima. Verifikasi email Anda; akun tetap menunggu aktivasi setelahnya.');
    }
}
