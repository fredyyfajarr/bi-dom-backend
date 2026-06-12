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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'year'       => 'sometimes|integer|min:2000|max:2099',
            'period'     => 'sometimes|string|in:year,month',
            'monthIndex' => 'sometimes|nullable|integer|min:0|max:11',
            'exclude'    => 'sometimes|nullable|string',
            'start_date' => 'sometimes|nullable|date',
            'end_date'   => 'sometimes|nullable|date',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
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
     * @return array{0: string, 1: string, 2: mixed, 3: array<int, string>, 4: ?string, 5: ?string, 6: ?int}
     */
    public function filters(): array
    {
        $safe = $this->validated();
        $excludeRaw = (string) ($safe['exclude'] ?? '');

        return [
            (string) ($safe['year'] ?? date('Y')),
            (string) ($safe['period'] ?? 'year'),
            $safe['monthIndex'] ?? null,
            $excludeRaw ? array_values(array_filter(explode(',', $excludeRaw))) : [],
            $safe['start_date'] ?? null,
            $safe['end_date'] ?? null,
            isset($safe['category_id']) ? (int) $safe['category_id'] : null,
        ];
    }
}
