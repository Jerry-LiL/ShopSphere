<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:255'],
            'secret' => ['required', 'string', 'min:8', 'max:255'],
            'event' => ['required', 'string', Rule::in(['order.created', 'order.updated', '*'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
