<?php

namespace App\Services;

use RuntimeException;

class IdentityDataProtector
{
    /**
     * @return array{ciphertext: string, nonce: string, tag: string}
     */
    public function encrypt(string $plaintext): array
    {
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $this->key('data_encryption'),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Unable to encrypt resident identity data.');
        }

        return ['ciphertext' => base64_encode($ciphertext), 'nonce' => bin2hex($nonce), 'tag' => bin2hex($tag)];
    }

    public function decrypt(string $ciphertext, string $nonce, string $tag): string
    {
        $rawCiphertext = base64_decode($ciphertext, true);
        $rawNonce = hex2bin($nonce);
        $rawTag = hex2bin($tag);

        if ($rawCiphertext === false || $rawNonce === false || $rawTag === false) {
            throw new RuntimeException('Encrypted resident identity data is malformed.');
        }

        $plaintext = openssl_decrypt(
            $rawCiphertext,
            'aes-256-gcm',
            $this->key('data_encryption'),
            OPENSSL_RAW_DATA,
            $rawNonce,
            $rawTag,
        );

        if ($plaintext === false) {
            throw new RuntimeException('Unable to decrypt resident identity data.');
        }

        return $plaintext;
    }

    public function blindIndex(string $value): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($value)), $this->key('identity_blind_index'));
    }

    private function key(string $keyName): string
    {
        $encodedKey = config('security.'.$keyName);

        if (! is_string($encodedKey) || ! str_starts_with($encodedKey, 'base64:')) {
            throw new RuntimeException('The required identity data key is not configured.');
        }

        $key = base64_decode(substr($encodedKey, 7), true);

        if (! is_string($key) || strlen($key) !== 32) {
            throw new RuntimeException('The configured identity data key must contain 32 random bytes.');
        }

        return $key;
    }
}
