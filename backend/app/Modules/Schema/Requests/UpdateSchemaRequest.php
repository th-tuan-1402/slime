<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\UpdateSchemaDto;

final class UpdateSchemaRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dbg_id' => ['sometimes', 'integer', 'min:0'],
            'db_schema_name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'db_schema_comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{dbg_id?:int, db_schema_name?:string, db_schema_comment?:string|null} $data */
        $data = $this->validated();

        return new UpdateSchemaDto(
            dbgId: array_key_exists('dbg_id', $data) ? (int) $data['dbg_id'] : null,
            dbSchemaName: array_key_exists('db_schema_name', $data) ? (string) $data['db_schema_name'] : null,
            dbSchemaComment: array_key_exists('db_schema_comment', $data) ? ($data['db_schema_comment'] !== null ? (string) $data['db_schema_comment'] : null) : null,
        );
    }
}

