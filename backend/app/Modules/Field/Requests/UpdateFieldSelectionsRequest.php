<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\UpdateFieldSelectionsDto;

final class UpdateFieldSelectionsRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'options' => ['required', 'array', 'min:1'],
            'options.*.value' => ['required', 'string', 'min:1', 'max:255', 'distinct'],
            'options.*.label' => ['required', 'string', 'min:1', 'max:255'],
            'options.*.order' => ['required', 'integer', 'min:0', 'distinct'],
            'options.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{options:list<array{value:string,label:string,order:int,is_active?:bool}>} $data */
        $data = $this->validated();

        $options = array_map(
            static fn(array $row): array => [
                'value' => (string) $row['value'],
                'label' => (string) $row['label'],
                'order' => (int) $row['order'],
                'is_active' => (bool) ($row['is_active'] ?? true),
            ],
            $data['options'],
        );

        return new UpdateFieldSelectionsDto($options);
    }
}

