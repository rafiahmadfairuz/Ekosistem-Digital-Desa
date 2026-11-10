<?php

namespace App\Http\Requests;

use App\Models\Seller;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSellerApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $seller = $user?->seller;

        return $user !== null && ($user->can('apply', Seller::class) || ($seller instanceof Seller && $user->can('update', $seller)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:160'],
            'business_description' => ['nullable', 'string', 'max:5000'],
            'business_address' => ['nullable', 'string', 'max:3000'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
