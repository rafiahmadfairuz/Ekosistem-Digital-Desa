<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendingUserController extends Controller
{
    public function index(AuditLogger $audit): View
    {
        $audit->record('account.pending_list_viewed', 'account_collection');
        $pendingUsers = User::query()
            ->where('account_status', 'pending')
            ->orderBy('created_at')
            ->simplePaginate(20);

        return view('admin.pending-users', ['pendingUsers' => $pendingUsers]);
    }

    public function activate(User $user, AuditLogger $audit): RedirectResponse
    {
        abort_if($user->email === null || ! $user->hasVerifiedEmail(), 422, 'Email harus diverifikasi sebelum aktivasi.');
        $wasActivated = DB::transaction(function () use ($user, $audit): bool {
            $pendingUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($pendingUser->account_status !== 'pending') {
                return false;
            }

            $pendingUser->account_status = 'active';
            $pendingUser->save();
            $audit->record('account.activated', 'user', (int) $pendingUser->getKey(), ['status' => 'active']);

            return true;
        });

        return redirect()->route('admin.users.index')->with(
            'status',
            $wasActivated ? 'Akun berhasil diaktifkan.' : 'Akun tersebut tidak lagi menunggu aktivasi.',
        );
    }

    public function resendVerification(User $user, AuditLogger $audit): RedirectResponse
    {
        abort_unless($user->account_status === 'pending', 404);

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Email akun tersebut sudah diverifikasi. Akun dapat diaktifkan sekarang.');
        }

        abort_if($user->email === null, 422, 'Akun ini belum memiliki alamat email untuk verifikasi.');

        $user->sendEmailVerificationNotification();
        $audit->record('account.email_verification_resent', 'user', (int) $user->getKey());

        return back()->with('status', 'Tautan verifikasi dikirim ulang untuk akun '.$user->username.'.');
    }
}
