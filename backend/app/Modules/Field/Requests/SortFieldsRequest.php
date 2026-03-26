<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\SortFieldsDto;

final class SortFieldsRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'field_ids' => ['required', 'array', 'min:1'],
            'field_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{field_ids:list<int>} $data */
        $data = $this->validated();

        return new SortFieldsDto(array_map('intval', $data['field_ids']));
    }
}

