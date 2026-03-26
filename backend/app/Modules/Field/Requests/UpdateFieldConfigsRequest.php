<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\UpdateFieldConfigsDto;
use Illuminate\Validation\ValidationException;

final class UpdateFieldConfigsRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'configs' => ['required', 'array', 'min:1'],
            'configs.*' => ['nullable'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{configs:array<string,mixed>} $data */
        $data = $this->validated();
        $configs = [];

        foreach ($data['configs'] as $key => $value) {
            if (is_bool($value) || is_int($value) || is_string($value) || $value === null) {
                $configs[(string) $key] = $value;
                continue;
            }

            throw ValidationException::withMessages([
                "configs.{$key}" => [sprintf('Invalid config value type for "%s".', (string) $key)],
            ]);
        }

        return new UpdateFieldConfigsDto($configs);
    }
}

