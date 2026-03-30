<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\CopySchemaDto;

final class CopySchemaRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'db_schema_name' => ['nullable', 'string', 'min:1', 'max:255'],
        ];
    }

    public function toDto(): CopySchemaDto
    {
        /** @var array{db_schema_name?:string|null} $data */
        $data = $this->validated();

        return new CopySchemaDto($data['db_schema_name'] ?? null);
    }
}

