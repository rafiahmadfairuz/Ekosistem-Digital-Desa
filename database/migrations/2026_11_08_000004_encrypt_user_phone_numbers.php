<?php

use App\Services\IdentityDataProtector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'phone_ciphertext')) {
                $table->text('phone_ciphertext')->nullable();
                $table->string('phone_nonce', 64)->nullable();
                $table->string('phone_tag', 64)->nullable();
            }
        });

        $protector = app(IdentityDataProtector::class);
        DB::table('users')->select(['id', 'phone', 'phone_ciphertext', 'phone_nonce', 'phone_tag'])
            ->whereNotNull('phone')->orderBy('id')->chunkById(100, function ($users) use ($protector): void {
                foreach ($users as $user) {
                    if ($user->phone_ciphertext !== null) {
                        $protector->decrypt($user->phone_ciphertext, $user->phone_nonce, $user->phone_tag);
                        DB::table('users')->where('id', $user->id)->update(['phone' => null]);

                        continue;
                    }

                    $encrypted = $protector->encrypt((string) $user->phone);
                    DB::table('users')->where('id', $user->id)->update([
                        'phone_ciphertext' => $encrypted['ciphertext'],
                        'phone_nonce' => $encrypted['nonce'],
                        'phone_tag' => $encrypted['tag'],
                        'phone' => null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        $protector = app(IdentityDataProtector::class);
        DB::table('users')->select(['id', 'phone_ciphertext', 'phone_nonce', 'phone_tag'])
            ->whereNotNull('phone_ciphertext')->orderBy('id')->chunkById(100, function ($users) use ($protector): void {
                foreach ($users as $user) {
                    $phone = $protector->decrypt($user->phone_ciphertext, $user->phone_nonce, $user->phone_tag);
                    DB::table('users')->where('id', $user->id)->update(['phone' => $phone]);
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone_ciphertext', 'phone_nonce', 'phone_tag']);
        });
    }
};
