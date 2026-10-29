<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        if (!$product) {
            return false;
        }

        return $this->user() && $this->user()->can('update', $product);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('id');

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'sku' => ['sometimes', 'required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($this->user() && $this->user()->isAdmin()) {
            $rules['store_id'] = ['sometimes', 'required', 'integer', 'exists:stores,id'];
        }

        return $rules;
    }
}
