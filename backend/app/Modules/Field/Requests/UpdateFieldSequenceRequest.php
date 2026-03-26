<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\UpdateFieldSequenceDto;
use Illuminate\Validation\Rule;

final class UpdateFieldSequenceRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prefix' => ['nullable', 'string', 'max:255'],
            'padding' => ['required', 'integer', 'min:1', 'max:10'],
            'next_value' => ['required', 'integer', 'min:1'],
            'step' => ['required', 'integer', 'min:1'],
            'reset_policy' => ['required', 'string', Rule::in(['none', 'daily', 'monthly', 'yearly'])],
        ];
    }

    public function toDto(): object
    {
        /** @var array{prefix?:string|null,padding:int,next_value:int,step:int,reset_policy:string} $data */
        $data = $this->validated();

        return new UpdateFieldSequenceDto(
            prefix: array_key_exists('prefix', $data) ? ($data['prefix'] !== null ? (string) $data['prefix'] : null) : null,
            padding: (int) $data['padding'],
            nextValue: (int) $data['next_value'],
            step: (int) $data['step'],
            resetPolicy: (string) $data['reset_policy'],
        );
    }
}

