<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['owner', 'staff'], true);
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'min:1'], 'unit_cost' => ['nullable', 'numeric', 'min:0'], 'receipt_number' => ['nullable', 'string', 'max:100'], 'moved_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:1000']];
    }
}
