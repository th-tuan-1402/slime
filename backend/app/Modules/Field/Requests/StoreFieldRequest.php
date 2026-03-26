<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\CreateFieldDto;

final class StoreFieldRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field_name' => ['required', 'string', 'min:1', 'max:255'],
            'data_type' => ['required', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{field_name:string,data_type:int,is_required?:bool,max_length?:int|null} $data */
        $data = $this->validated();

        return new CreateFieldDto(
            fieldName: (string) $data['field_name'],
            dataType: (int) $data['data_type'],
            isRequired: (bool) ($data['is_required'] ?? false),
            maxLength: array_key_exists('max_length', $data) ? ($data['max_length'] !== null ? (int) $data['max_length'] : null) : null,
        );
    }
}

