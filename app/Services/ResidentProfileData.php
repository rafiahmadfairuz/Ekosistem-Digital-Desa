<?php

namespace App\Services;

use App\Models\Resident;

class ResidentProfileData
{
    public function __construct(private readonly IdentityDataProtector $protector) {}

    /** @param array<string, mixed> $data */
    public function apply(Resident $resident, array $data): void
    {
        foreach (['full_name', 'phone', 'address', 'birth_date', 'gender'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = trim((string) ($data[$field] ?? ''));
            $prefix = match ($field) {
                'full_name' => 'full_name',
                default => $field,
            };

            if ($value === '') {
                foreach ([$prefix.'_ciphertext', $prefix.'_nonce', $prefix.'_tag'] as $attribute) {
                    $resident->{$attribute} = null;
                }

                continue;
            }

            $encrypted = $this->protector->encrypt($value);
            $resident->{$prefix.'_ciphertext'} = $encrypted['ciphertext'];
            $resident->{$prefix.'_nonce'} = $encrypted['nonce'];
            $resident->{$prefix.'_tag'} = $encrypted['tag'];

        }

    }

    /** @return array{full_name: string, phone: string, address: string, birth_date: mixed, gender: mixed} */
    public function forOwner(Resident $resident): array
    {
        return [
            'full_name' => $this->decrypt($resident, 'full_name'),
            'phone' => $this->decrypt($resident, 'phone'),
            'address' => $this->decrypt($resident, 'address'),
            'birth_date' => $this->decrypt($resident, 'birth_date'),
            'gender' => $this->decrypt($resident, 'gender'),
        ];
    }

    public function visibleName(Resident $resident): string
    {
        return $this->decrypt($resident, 'full_name');
    }

    private function decrypt(Resident $resident, string $field): string
    {
        $ciphertext = $resident->{$field.'_ciphertext'};
        $nonce = $resident->{$field.'_nonce'};
        $tag = $resident->{$field.'_tag'};

        if (! is_string($ciphertext) || ! is_string($nonce) || ! is_string($tag)) {
            return '';
        }

        return $this->protector->decrypt($ciphertext, $nonce, $tag);
    }
}
