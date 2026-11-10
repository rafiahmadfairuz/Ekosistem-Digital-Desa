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
            if (! Schema::hasColumn('users', 'name_ciphertext')) {
                $table->text('name_ciphertext')->nullable();
                $table->string('name_nonce', 64)->nullable();
                $table->string('name_tag', 64)->nullable();
            }
        });

        $protector = app(IdentityDataProtector::class);
        DB::table('users')->select(['id', 'name', 'name_ciphertext', 'name_nonce', 'name_tag'])
            ->whereNotNull('name')->orderBy('id')->chunkById(100, function ($users) use ($protector): void {
                foreach ($users as $user) {
                    if ($user->name_ciphertext !== null) {
                        $protector->decrypt($user->name_ciphertext, $user->name_nonce, $user->name_tag);
                        if ($user->name !== 'account-'.$user->id) {
                            DB::table('users')->where('id', $user->id)->update(['name' => 'account-'.$user->id]);
                        }

                        continue;
                    }

                    $encrypted = $protector->encrypt((string) $user->name);
                    DB::table('users')->where('id', $user->id)->update([
                        'name_ciphertext' => $encrypted['ciphertext'],
                        'name_nonce' => $encrypted['nonce'],
                        'name_tag' => $encrypted['tag'],
                        'name' => 'account-'.$user->id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        $protector = app(IdentityDataProtector::class);
        DB::table('users')->select(['id', 'name_ciphertext', 'name_nonce', 'name_tag'])
            ->whereNotNull('name_ciphertext')->orderBy('id')->chunkById(100, function ($users) use ($protector): void {
                foreach ($users as $user) {
                    $name = $protector->decrypt($user->name_ciphertext, $user->name_nonce, $user->name_tag);
                    DB::table('users')->where('id', $user->id)->update(['name' => $name]);
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['name_ciphertext', 'name_nonce', 'name_tag']);
        });
    }
};
