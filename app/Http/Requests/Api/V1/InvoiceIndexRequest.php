<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceIndexRequest extends FormRequest
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
            'search'      => 'sometimes|nullable|string|max:255',
            'sort_by'     => 'sometimes|string|in:created_at,trx_date,total_amount,receipt_no,payment_method',
            'sort_dir'    => 'sometimes|string|in:asc,desc',
            'filter_date' => 'sometimes|string',
            'per_page'    => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * For GET requests, merge query parameters so they can be validated.
     */
    protected function prepareForValidation(): void
    {
        $this->merge($this->query());
    }

    /**
     * @return array{search: string, sort_by: string, sort_dir: string, filter_date: string, per_page: int}
     */
    public function filters(): array
    {
        $safe = $this->validated();

        return [
            'search'      => (string) ($safe['search'] ?? ''),
            'sort_by'     => (string) ($safe['sort_by'] ?? 'trx_date'),
            'sort_dir'    => (string) ($safe['sort_dir'] ?? 'desc'),
            'filter_date' => (string) ($safe['filter_date'] ?? 'all'),
            'per_page'    => (int) ($safe['per_page'] ?? 15),
        ];
    }
}
