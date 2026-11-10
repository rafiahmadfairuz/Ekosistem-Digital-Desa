<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLetterTypeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['required_fields', 'required_attachments'] as $key) {
            $value = $this->input($key);
            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }

            if (! is_array($value)) {
                if ($value === null || (is_string($value) && trim($value) === '')) {
                    $value = [];
                } else {
                    continue;
                }
            }

            $value = array_values($value);
            $usedKeys = [];
            foreach ($value as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $label = trim((string) ($item['label'] ?? ''));
                $item['label'] = $label;
                $item['key'] = trim((string) ($item['key'] ?? '')) ?: $this->makeKey($label, $usedKeys);
                $usedKeys[] = $item['key'];
                if (array_key_exists('required', $item)) {
                    $item['required'] = in_array($item['required'], [true, 1, '1'], true);
                }

                if ($key === 'required_fields') {
                    if (array_key_exists('options_text', $item)) {
                        $item['options'] = ($item['type'] ?? null) === 'select'
                            ? array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $item['options_text']) ?: []), fn (string $option): bool => $option !== ''))
                            : [];
                        unset($item['options_text']);
                    }
                } elseif (array_key_exists('max_mb', $item)) {
                    $item['max_kb'] = is_numeric($item['max_mb'])
                        ? (int) round((float) $item['max_mb'] * 1024)
                        : $item['max_mb'];
                    unset($item['max_mb']);
                }

                $value[$index] = $item;
            }

            $this->merge([$key => $value]);
        }
    }

    /** @param list<string> $usedKeys */
    private function makeKey(string $label, array $usedKeys): string
    {
        $base = Str::slug($label, '_');
        $base = preg_replace('/[^a-z0-9_]/', '', $base) ?: 'field';
        if (! preg_match('/^[a-z]/', $base)) {
            $base = 'field_'.$base;
        }
        $base = substr($base, 0, 60);
        $key = $base;
        $suffix = 2;

        while (in_array($key, $usedKeys, true)) {
            $ending = '_'.$suffix++;
            $key = substr($base, 0, 60 - strlen($ending)).$ending;
        }

        return $key;
    }

    public function authorize(): bool
    {
        return $this->user()?->hasRole('Village Admin') ?? false;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'code' => 'kode internal',
            'name' => 'nama jenis surat',
            'required_fields.*.key' => 'identitas informasi',
            'required_fields.*.label' => 'nama informasi',
            'required_fields.*.type' => 'cara warga mengisi',
            'required_fields.*.max' => 'batas panjang jawaban',
            'required_fields.*.options' => 'daftar pilihan',
            'required_attachments.*.key' => 'identitas dokumen',
            'required_attachments.*.label' => 'nama dokumen',
            'required_attachments.*.max_kb' => 'batas ukuran file dalam MB',
        ];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $letterType = $this->route('letterType');

        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('letter_types', 'code')->ignore($letterType?->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['sometimes', 'boolean'],
            'required_fields' => ['nullable', 'array', 'max:30'],
            'required_fields.*.key' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            'required_fields.*.label' => ['required', 'string', 'max:120'],
            'required_fields.*.type' => ['required', Rule::in(['text', 'textarea', 'date', 'integer', 'select'])],
            'required_fields.*.required' => ['sometimes', 'boolean'],
            'required_fields.*.max' => ['sometimes', 'integer', 'min:1', 'max:2000'],
            'required_fields.*.options' => ['sometimes', 'array', 'max:50'],
            'required_fields.*.options.*' => ['string', 'max:120'],
            'required_attachments' => ['nullable', 'array', 'max:15'],
            'required_attachments.*.key' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            'required_attachments.*.label' => ['required', 'string', 'max:120'],
            'required_attachments.*.required' => ['sometimes', 'boolean'],
            'required_attachments.*.max_kb' => ['sometimes', 'integer', 'min:1', 'max:10240'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $fields = $this->input('required_fields', []);
            if (! is_array($fields)) {
                return;
            }

            foreach ($fields as $index => $field) {
                if (! is_array($field)) {
                    continue;
                }

                if (($field['type'] ?? null) === 'select' && empty($field['options'])) {
                    $validator->errors()->add('required_fields.'.$index.'.options', 'Pilihan wajib diisi untuk isian bertipe select.');
                }
            }
        }];
    }
}
