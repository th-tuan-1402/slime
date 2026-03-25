<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;

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

    public function toDto(): object
    {
        /** @var array{group_ids:list<int>} $data */
        $data = $this->validated();

        return (object) [
            'groupIds' => array_map('intval', $data['group_ids']),
        ];
    }
}

