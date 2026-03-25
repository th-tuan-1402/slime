<?php

declare(strict_types=1);

namespace App\Modules\Schema\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Schema\Dtos\CreateSchemaGroupDto;

final class StoreSchemaGroupRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dbg_name' => ['required', 'string', 'min:1', 'max:255'],
            'dbg_comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{dbg_name:string, dbg_comment?:string|null} $data */
        $data = $this->validated();

        return new CreateSchemaGroupDto(
            dbgName: (string) $data['dbg_name'],
            dbgComment: array_key_exists('dbg_comment', $data) ? ($data['dbg_comment'] !== null ? (string) $data['dbg_comment'] : null) : null,
        );
    }
}

