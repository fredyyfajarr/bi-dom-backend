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
     * Keep invoice query validation permissive because the service already
     * sanitizes sorting and filtering defaults used by the current API.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{search: string, sort_by: string, sort_dir: string, filter_date: string, per_page: int}
     */
    public function filters(): array
    {
        return [
            'search' => (string) $this->query('search', ''),
            'sort_by' => (string) $this->query('sort_by', 'created_at'),
            'sort_dir' => (string) $this->query('sort_dir', 'desc'),
            'filter_date' => (string) $this->query('filter_date', 'all'),
            'per_page' => (int) $this->query('per_page', 15),
        ];
    }
}
