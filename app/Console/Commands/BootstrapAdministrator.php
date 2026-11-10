<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use LogicException;

#[Signature('app:bootstrap-administrator')]
#[Description('Buat akun Super Admin teknis pertama. Perintah ini hanya dapat berhasil sekali.')]
class BootstrapAdministrator extends Command
{
    public function handle(): int
    {
        $role = Role::query()->where('name', 'Super Admin')->first();

        if ($role === null) {
            $this->components->error('Peran belum tersedia. Jalankan php artisan db:seed --class=RolesSeeder terlebih dahulu.');

            return self::FAILURE;
        }

        if ($role->users()->exists()) {
            $this->components->error('Akun Super Admin sudah ada. Perintah ini hanya dapat digunakan sekali.');

            return self::FAILURE;
        }

        $username = mb_strtolower(trim((string) $this->ask('Nama pengguna')));
        $email = mb_strtolower(trim((string) $this->ask('Email')));
        $password = (string) $this->secret('Kata sandi baru');
        $passwordConfirmation = (string) $this->secret('Ulangi kata sandi');

        $validator = Validator::make([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'username' => ['required', 'string', 'alpha_dash:ascii', 'min:3', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($username, $email, $password): void {
                $superAdminRole = Role::query()
                    ->where('name', 'Super Admin')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($superAdminRole->users()->exists()) {
                    throw new LogicException('Akun Super Admin sudah ada.');
                }

                $user = new User;
                $user->name = 'account-'.$username;
                $user->username = $username;
                $user->email = $email;
                $user->password = $password;
                $user->account_status = 'active';
                $user->save();
                $user->roles()->attach($superAdminRole->getKey());
                app(AuditLogger::class)->record('account.bootstrap_super_admin_created', 'user', (int) $user->getKey());
            });
        } catch (LogicException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Akun Super Admin berhasil dibuat. Masuk menggunakan nama pengguna atau email yang Anda isi.');
        User::query()->where('username', $username)->firstOrFail()->sendEmailVerificationNotification();
        $this->components->info('Tautan verifikasi dikirim ke email administrator.');
        $this->components->warn('Super Admin adalah peran teknis dan tidak memiliki kewenangan tanda tangan Kepala Desa.');

        return self::SUCCESS;
    }
}
