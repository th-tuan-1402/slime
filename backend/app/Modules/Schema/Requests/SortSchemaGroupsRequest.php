<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\SortSchemaGroupsDto;

final class SortSchemaGroupsRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): SortSchemaGroupsDto
    {
        /** @var array{group_ids:list<int>} $data */
        $data = $this->validated();

        return new SortSchemaGroupsDto(array_map('intval', $data['group_ids']));
    }
}

