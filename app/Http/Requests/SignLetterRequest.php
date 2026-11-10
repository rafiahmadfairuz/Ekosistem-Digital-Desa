<?php

namespace App\Http\Requests;

use App\Models\LetterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $letterRequest = $this->route('letterRequest');

        return $letterRequest instanceof LetterRequest
            && ($this->user()?->can('sign', $letterRequest) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['signed', 'rejected'])],
            'comments' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
            'prototype_acknowledged' => ['required_if:decision,signed', 'accepted'],
        ];
    }
}
