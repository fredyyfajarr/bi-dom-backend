<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Query parameters remain permissive to preserve the current dashboard API.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{0: string, 1: string, 2: mixed, 3: array<int, string>}
     */
    public function filters(): array
    {
        $excludeRaw = (string) $this->query('exclude', '');

        return [
            (string) $this->query('year', date('Y')),
            (string) $this->query('period', 'year'),
            $this->query('monthIndex'),
            $excludeRaw ? array_values(array_filter(explode(',', $excludeRaw))) : [],
        ];
    }
}
