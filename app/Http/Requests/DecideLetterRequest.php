<?php

namespace App\Http\Requests;

use App\Models\LetterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $letterRequest = $this->route('letterRequest');

        return $letterRequest instanceof LetterRequest
            && ($this->user()?->can('review', $letterRequest) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected', 'revision_required'])],
            'comments' => ['required_if:decision,rejected,revision_required', 'nullable', 'string', 'max:2000'],
        ];
    }
}
