<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\CreateSchemaDto;

final class StoreSchemaRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dbg_id' => ['required', 'integer', 'min:0'],
            'db_schema_name' => ['required', 'string', 'min:1', 'max:255'],
            'db_schema_comment' => ['nullable', 'string', 'max:2000'],
            'schema_type' => ['sometimes', 'integer', 'in:0,1,2,3'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{dbg_id:int, db_schema_name:string, db_schema_comment?:string|null, schema_type?:int} $data */
        $data = $this->validated();

        return new CreateSchemaDto(
            dbgId: (int) $data['dbg_id'],
            dbSchemaName: (string) $data['db_schema_name'],
            dbSchemaComment: array_key_exists('db_schema_comment', $data) ? ($data['db_schema_comment'] !== null ? (string) $data['db_schema_comment'] : null) : null,
            schemaType: array_key_exists('schema_type', $data) ? (int) $data['schema_type'] : 0,
        );
    }
}

