<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1'], 'reference_number' => ['nullable', 'string', 'max:100'], 'moved_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:1000']];
    }
}
