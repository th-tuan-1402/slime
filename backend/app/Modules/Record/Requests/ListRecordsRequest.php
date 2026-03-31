<?php

declare(strict_types=1);

namespace App\Modules\Record\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates list/search/sort/pagination query params for dynamic schema records.
 *
 * `filters` is a JSON-encoded object string; it will be parsed in the controller
 * with defensive try/catch to avoid hard failures on invalid input.
 */
final class ListRecordsRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'in:10,20,50,100'],
            'sortBy' => ['nullable', 'string', 'max:255'],
            'sortDir' => ['nullable', 'in:asc,desc'],
            'q' => ['nullable', 'string', 'max:255'],
            'filters' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

