<?php

namespace Tests\Unit;

use App\Services\IdentityDataProtector;
use RuntimeException;
use Tests\TestCase;

class IdentityDataProtectorTest extends TestCase
{
    public function test_identity_values_round_trip_and_blind_index_is_deterministic(): void
    {
        config([
            'security.data_encryption' => 'base64:'.base64_encode(random_bytes(32)),
            'security.identity_blind_index' => 'base64:'.base64_encode(random_bytes(32)),
        ]);
        $protector = new IdentityDataProtector;
        $encrypted = $protector->encrypt('Nama Contoh');

        $this->assertSame('Nama Contoh', $protector->decrypt($encrypted['ciphertext'], $encrypted['nonce'], $encrypted['tag']));
        $this->assertSame($protector->blindIndex('ABC 123'), $protector->blindIndex(' abc 123 '));
        $this->assertNotSame('Nama Contoh', $encrypted['ciphertext']);
    }

    public function test_missing_key_fails_closed(): void
    {
        config(['security.data_encryption' => null]);

        $this->expectException(RuntimeException::class);
        (new IdentityDataProtector)->encrypt('Nama Contoh');
    }
}
