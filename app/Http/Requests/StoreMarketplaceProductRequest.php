<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMarketplaceProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            ? ($this->user()?->can('update', $product) ?? false)
            : ($this->user()?->can('create', Product::class) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'unit' => ['required', 'string', 'max:40'],
            'price' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'images' => ['sometimes', 'array', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
