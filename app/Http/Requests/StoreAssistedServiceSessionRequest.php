<?php

namespace App\Http\Requests;

use App\Models\AssistedServiceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAssistedServiceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AssistedServiceSession::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::in(AssistedServiceSession::SERVICE_TYPES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $notes = (string) $this->input('notes', '');
            if (preg_match('/\b(password|kata sandi|otp|one[- ]time|pin|cvv|cvc|nomor kartu|token|secret|kata kunci)\b/i', $notes) === 1) {
                $validator->errors()->add('notes', 'Catatan tidak boleh memuat kata sandi, kode, PIN, token, atau kredensial. Hapus bagian tersebut dan tulis ringkasan umum.');
            }

        }];
    }
}
