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
        Schema::table('residents', function (Blueprint $table): void {
            if (! Schema::hasColumn('residents', 'birth_date_ciphertext')) {
                $table->text('birth_date_ciphertext')->nullable();
                $table->string('birth_date_nonce', 64)->nullable();
                $table->string('birth_date_tag', 64)->nullable();
            }
            if (! Schema::hasColumn('residents', 'gender_ciphertext')) {
                $table->text('gender_ciphertext')->nullable();
                $table->string('gender_nonce', 64)->nullable();
                $table->string('gender_tag', 64)->nullable();
            }
        });

        $protector = app(IdentityDataProtector::class);
        DB::table('residents')->select(['id', 'birth_date', 'gender', 'birth_date_ciphertext', 'gender_ciphertext'])
            ->where(function ($query): void {
                $query->whereNotNull('birth_date')->orWhereNotNull('gender');
            })->orderBy('id')->chunkById(100, function ($residents) use ($protector): void {
                foreach ($residents as $resident) {
                    $updates = [];
                    if ($resident->birth_date_ciphertext !== null) {
                        $protector->decrypt($resident->birth_date_ciphertext, $resident->birth_date_nonce, $resident->birth_date_tag);
                        if ($resident->birth_date !== null) {
                            $updates['birth_date'] = null;
                        }
                    } elseif ($resident->birth_date !== null) {
                        $encrypted = $protector->encrypt((string) $resident->birth_date);
                        $updates += ['birth_date_ciphertext' => $encrypted['ciphertext'], 'birth_date_nonce' => $encrypted['nonce'], 'birth_date_tag' => $encrypted['tag'], 'birth_date' => null];
                    }
                    if ($resident->gender_ciphertext !== null) {
                        $protector->decrypt($resident->gender_ciphertext, $resident->gender_nonce, $resident->gender_tag);
                        if ($resident->gender !== null) {
                            $updates['gender'] = null;
                        }
                    } elseif ($resident->gender !== null) {
                        $encrypted = $protector->encrypt((string) $resident->gender);
                        $updates += ['gender_ciphertext' => $encrypted['ciphertext'], 'gender_nonce' => $encrypted['nonce'], 'gender_tag' => $encrypted['tag'], 'gender' => null];
                    }
                    if ($updates !== []) {
                        DB::table('residents')->where('id', $resident->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        $protector = app(IdentityDataProtector::class);
        DB::table('residents')->select(['id', 'birth_date_ciphertext', 'birth_date_nonce', 'birth_date_tag', 'gender_ciphertext', 'gender_nonce', 'gender_tag'])
            ->where(function ($query): void {
                $query->whereNotNull('birth_date_ciphertext')->orWhereNotNull('gender_ciphertext');
            })->orderBy('id')->chunkById(100, function ($residents) use ($protector): void {
                foreach ($residents as $resident) {
                    $updates = [];
                    if ($resident->birth_date_ciphertext !== null) {
                        $updates['birth_date'] = $protector->decrypt($resident->birth_date_ciphertext, $resident->birth_date_nonce, $resident->birth_date_tag);
                    }
                    if ($resident->gender_ciphertext !== null) {
                        $updates['gender'] = $protector->decrypt($resident->gender_ciphertext, $resident->gender_nonce, $resident->gender_tag);
                    }
                    if ($updates !== []) {
                        DB::table('residents')->where('id', $resident->id)->update($updates);
                    }
                }
            });

        Schema::table('residents', function (Blueprint $table): void {
            $table->dropColumn([
                'birth_date_ciphertext', 'birth_date_nonce', 'birth_date_tag',
                'gender_ciphertext', 'gender_nonce', 'gender_tag',
            ]);
        });
    }
};
