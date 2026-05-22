<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportCsvRequest extends FormRequest
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
            'file' => 'nullable|file|mimes:csv,txt|max:5120',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:5120',
        ];
    }

    public function uploadedCsv(): ?UploadedFile
    {
        return $this->file('file') ?? $this->file('csv_file');
    }
}
