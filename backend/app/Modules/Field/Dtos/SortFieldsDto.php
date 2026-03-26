<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

final readonly class SortFieldsDto
{
    /**
     * @param list<int> $fieldIds
     */
    public function __construct(
        public array $fieldIds,
    ) {
    }
}

