<?php

namespace App\Http\Requests;

use App\Models\Cart;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketplaceCheckoutRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['payment_method' => $this->input('payment_method', 'cod')]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('checkout', Cart::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $xenditCheckoutReady = (bool) config('services.xendit.marketplace_enabled')
            && in_array(config('services.xendit.mode'), ['test', 'live'], true)
            && filled(config('services.xendit.secret_key'))
            && filled(config('services.xendit.webhook_token'));

        return [
            'delivery_method' => ['required', 'in:delivery,pickup'],
            'payment_method' => ['required', Rule::in($xenditCheckoutReady ? ['cod', 'xendit'] : ['cod'])],
            'delivery_address' => ['required_if:delivery_method,delivery', 'nullable', 'string', 'max:3000'],
            'recipient_name' => ['required', 'string', 'max:180'],
            'recipient_phone' => ['required', 'string', 'max:40'],
            'request_key' => ['required', 'string', 'size:64', 'alpha_dash'],
        ];
    }
}
