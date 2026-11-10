<?php

namespace App\Http\Requests;

use App\Models\Seller;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewMarketplaceSellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('review', Seller::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:under_review,approved,rejected,suspended,inactive'],
            'reason' => ['nullable', 'string', 'max:2000', 'required_if:status,rejected'],
        ];
    }
}
