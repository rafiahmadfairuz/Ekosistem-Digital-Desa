<?php

namespace App\Http\Requests;

use App\Models\AssistedServiceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssistedServiceMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session instanceof AssistedServiceSession
            && ($this->user()?->can('sendMessage', $session) ?? false);
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:1', 'max:4000']];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => trim($this->input('body'))]);
        }
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $body = (string) $this->input('body', '');
            if (preg_match('/\b(password|passphrase|kata sandi|otp|one[- ]time|pin|cvv|cvc|nomor kartu|token rahasia|secret)\b/i', $body) === 1) {
                $validator->errors()->add('body', 'Jangan kirim kata sandi, OTP, PIN, atau data pembayaran melalui percakapan.');
            }
        }];
    }
}
