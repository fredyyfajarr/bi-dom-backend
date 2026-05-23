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
}
