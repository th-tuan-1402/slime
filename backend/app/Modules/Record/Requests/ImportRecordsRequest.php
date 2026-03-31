<?php

declare(strict_types=1);

namespace App\Modules\Record\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImportRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:text/plain,text/csv,application/vnd.ms-excel', 'max:10240'],
        ];
    }
}

