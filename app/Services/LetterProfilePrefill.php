<?php

namespace App\Services;

class LetterProfilePrefill
{
    /** @param list<array<string, mixed>> $fields
     * @param  array<string, mixed>  $profile
     * @return array<string, string>
     */
    public function values(array $fields, array $profile): array
    {
        $values = [];

        foreach ($fields as $field) {
            $key = (string) ($field['key'] ?? '');
            $profileKey = $this->profileKey($key) ?? $this->profileKey((string) ($field['label'] ?? ''));
            if ($profileKey === null || ! isset($profile[$profileKey]) || trim((string) $profile[$profileKey]) === '') {
                continue;
            }

            $value = (string) $profile[$profileKey];
            if ($profileKey === 'gender') {
                $value = $this->genderValue($value, $field);
            }

            if (($field['type'] ?? null) === 'select') {
                $option = collect($field['options'] ?? [])->first(fn ($option): bool => mb_strtolower(trim((string) $option)) === mb_strtolower(trim($value)));
                if ($option === null) {
                    continue;
                }
                $value = (string) $option;
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private function profileKey(string $identifier): ?string
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $identifier));

        return match (true) {
            in_array($normalized, ['fullname', 'name', 'nama'], true)
                || str_contains($normalized, 'namalengkap')
                || str_contains($normalized, 'namapemohon') => 'full_name',
            in_array($normalized, ['phone', 'telephone', 'phonenumber', 'telepon', 'nomortelepon', 'nomorhp', 'nohp'], true)
                || str_contains($normalized, 'nomortelepon')
                || str_contains($normalized, 'nomorhp')
                || str_contains($normalized, 'telepon') => 'phone',
            str_contains($normalized, 'email') => 'email',
            in_array($normalized, ['address', 'alamat'], true)
                || str_contains($normalized, 'alamat') => 'address',
            in_array($normalized, ['birthdate', 'dateofbirth', 'tanggallahir'], true)
                || str_contains($normalized, 'tanggallahir') => 'birth_date',
            in_array($normalized, ['gender', 'sex', 'jeniskelamin'], true)
                || str_contains($normalized, 'jeniskelamin') => 'gender',
            default => null,
        };
    }

    /** @param array<string, mixed> $field */
    private function genderValue(string $value, array $field): string
    {
        $labels = [
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            'other' => 'Lainnya',
            'undisclosed' => 'Tidak ingin menyebutkan',
        ];
        $label = $labels[strtolower($value)] ?? $value;
        if (($field['type'] ?? null) !== 'select') {
            return $label;
        }

        foreach ($field['options'] ?? [] as $option) {
            if (strtolower((string) $option) === strtolower($value) || strtolower((string) $option) === strtolower($label)) {
                return (string) $option;
            }
        }

        return $label;
    }
}
