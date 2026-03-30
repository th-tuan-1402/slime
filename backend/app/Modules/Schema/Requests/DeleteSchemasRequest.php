<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\DeleteSchemasDto;

final class DeleteSchemasRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'schema_ids' => ['required', 'array', 'min:1'],
            'schema_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): DeleteSchemasDto
    {
        /** @var array{schema_ids:list<int>} $data */
        $data = $this->validated();

        return new DeleteSchemasDto(array_map('intval', $data['schema_ids']));
    }
}
