<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name'                     => 'required|string|max:255',
            'category_id'              => 'required|integer|exists:categories,id',
            'price'                    => 'required|numeric|min:0',
            'materials'                => 'nullable|array',
            'materials.*.inventory_id' => 'required_with:materials|integer|exists:inventories,id',
            'materials.*.usage_qty'    => 'required_with:materials|numeric|min:0.01',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        return $this->validated();
    }
}
