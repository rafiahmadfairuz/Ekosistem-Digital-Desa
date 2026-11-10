<?php

namespace App\Http\Requests;

use App\Models\AssistedServiceSession;
use App\Models\LetterRequest;
use App\Models\LetterType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $letterRequest = $this->route('letterRequest');

        return $letterRequest instanceof LetterRequest
            ? ($user?->can('update', $letterRequest) ?? false)
            : ($user?->can('create', LetterRequest::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $type = $this->route('letterRequest')?->effectiveLetterType()
            ?? LetterType::query()->find($this->input('letter_type_id'));
        $assistedActor = ($this->user()?->hasRole('BUMDes Operator') || $this->user()?->hasRole('BUMDes Manager')) && ! $this->user()?->hasRole('Resident');
        $rules = [
            'letter_type_id' => [$this->route('letterRequest') ? 'prohibited' : 'required', 'integer', 'exists:letter_types,id'],
            'resident_id' => ['prohibited'],
            'assistance_session_id' => $assistedActor && ! $this->route('letterRequest')
                ? ['required', 'integer', 'exists:assisted_service_sessions,id']
                : ['prohibited'],
            'resident_username' => ['prohibited'],
            'resident_confirmation' => $this->user()?->hasRole('Resident') && ! $this->route('letterRequest')
                ? ['required', 'accepted']
                : ['prohibited'],
            'fields' => ['required', 'array', 'max:30'],
            'attachments' => ['nullable', 'array', 'max:15'],
        ];

        foreach ($type?->required_fields ?? [] as $field) {
            $key = 'fields.'.$field['key'];
            $rules[$key] = [($field['required'] ?? true) ? 'required' : 'nullable'];
            if ($this->isNikField($field)) {
                $rules[$key][] = 'string';
                $rules[$key][] = 'max:16';
                $rules[$key][] = 'regex:/^[0-9]{16}$/';
            } elseif ($field['type'] === 'integer') {
                $rules[$key][] = 'integer';
            } elseif ($field['type'] === 'date') {
                $rules[$key][] = 'date_format:Y-m-d';
            } else {
                $rules[$key][] = 'string';
                $rules[$key][] = 'max:'.min((int) ($field['max'] ?? 255), 2000);
            }

            if ($field['type'] === 'select') {
                $rules[$key][] = Rule::in($field['options'] ?? []);
            }
        }

        foreach ($type?->required_attachments ?? [] as $attachment) {
            $rules['attachments.'.$attachment['key']] = [
                ($attachment['required'] ?? true) && ! $this->route('letterRequest') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:'.min((int) ($attachment['max_kb'] ?? 5120), 10240),
            ];
        }

        return $rules;
    }

    /** @param array<string, mixed> $field */
    private function isNikField(array $field): bool
    {
        return in_array(strtolower((string) ($field['key'] ?? '')), ['nik', 'national_id'], true)
            || preg_match('/\bnik\b|nomor induk kependudukan/i', (string) ($field['label'] ?? '')) === 1;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $files = $this->file('attachments', []);
            foreach ($files as $file) {
                if ($file !== null && $file->isValid() && ! in_array(strtolower($file->getClientOriginalExtension()), ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                    $validator->errors()->add('attachments', 'Lampiran harus berupa PDF, JPG, JPEG, atau PNG.');
                }
            }

            $user = $this->user();
            $assistedActor = ($user?->hasRole('BUMDes Operator') || $user?->hasRole('BUMDes Manager')) && ! $user?->hasRole('Resident');
            if (! $assistedActor || $this->route('letterRequest') || $validator->errors()->has('assistance_session_id')) {
                return;
            }

            $session = AssistedServiceSession::query()->whereKey($this->input('assistance_session_id'))
                ->where('operator_id', $this->user()?->getKey())
                ->where('service_type', 'correspondence')->where('status', 'in_progress')
                ->whereHas('resident', fn ($resident) => $resident->whereNotNull('region_verified_at'))
                ->first();
            if ($session === null) {
                $validator->errors()->add('assistance_session_id', 'Pilih permintaan persuratan yang sudah Anda terima dan masih aktif.');
            }
        }];
    }
}
