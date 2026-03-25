<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\UpdateSchemaGroupDto;

final class UpdateSchemaGroupRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dbg_name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'dbg_comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{dbg_name?:string, dbg_comment?:string|null} $data */
        $data = $this->validated();

        return new UpdateSchemaGroupDto(
            dbgName: array_key_exists('dbg_name', $data) ? (string) $data['dbg_name'] : null,
            dbgComment: array_key_exists('dbg_comment', $data) ? ($data['dbg_comment'] !== null ? (string) $data['dbg_comment'] : null) : null,
        );
    }
}

