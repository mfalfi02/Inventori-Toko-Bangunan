<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return ['category_id' => ['nullable', 'exists:categories,id'], 'sku' => ['required', 'string', 'max:50', 'unique:products,sku'], 'name' => ['required', 'string', 'max:150'], 'unit' => ['required', 'string', 'max:20'], 'minimum_stock' => ['required', 'integer', 'min:0']];
    }
}
