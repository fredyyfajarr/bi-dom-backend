<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
            'inventory_id' => 'required|exists:inventories,id',
            'added_stock' => 'required|numeric|min:0',
        ];
    }

    public function inventoryId(): int
    {
        return (int) $this->validated('inventory_id');
    }

    public function addedStock(): float
    {
        return (float) $this->validated('added_stock');
    }
}
