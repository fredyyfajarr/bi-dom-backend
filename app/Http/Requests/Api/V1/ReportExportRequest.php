<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReportExportRequest extends FormRequest
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
            'days' => 'sometimes|integer|min:1|max:365',
            'year' => 'sometimes|integer|min:2020|max:2035',
            'period' => 'sometimes|in:year,month',
            'monthIndex' => 'sometimes|integer|min:0|max:11',
            'exclude' => 'sometimes|nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'category_id' => 'sometimes|integer|exists:categories,id',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->query());
    }

    public function days(): int
    {
        return (int) ($this->validated()['days'] ?? 30);
    }

    /**
     * @return array{days: int, year: int|null, period: string, monthIndex: int|null, exclude: array<int, string>, start_date: string|null, end_date: string|null, category_id: int|null}
     */
    public function filters(): array
    {
        $validated = $this->validated();
        $excludeRaw = (string) ($validated['exclude'] ?? '');

        return [
            'days' => $this->days(),
            'year' => isset($validated['year']) ? (int) $validated['year'] : null,
            'period' => $validated['period'] ?? 'year',
            'monthIndex' => isset($validated['monthIndex']) ? (int) $validated['monthIndex'] : null,
            'exclude' => $excludeRaw ? array_values(array_filter(explode(',', $excludeRaw))) : [],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
        ];
    }
}
