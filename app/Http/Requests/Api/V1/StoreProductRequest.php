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
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'materials' => 'array',
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
