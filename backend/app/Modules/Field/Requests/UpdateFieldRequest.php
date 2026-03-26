<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\UpdateFieldDto;

final class UpdateFieldRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field_name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'data_type' => ['sometimes', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{field_name?:string,data_type?:int,is_required?:bool,max_length?:int|null} $data */
        $data = $this->validated();

        return new UpdateFieldDto(
            fieldName: array_key_exists('field_name', $data) ? (string) $data['field_name'] : null,
            dataType: array_key_exists('data_type', $data) ? (int) $data['data_type'] : null,
            isRequired: array_key_exists('is_required', $data) ? (bool) $data['is_required'] : null,
            maxLength: array_key_exists('max_length', $data) ? ($data['max_length'] !== null ? (int) $data['max_length'] : null) : null,
        );
    }
}

