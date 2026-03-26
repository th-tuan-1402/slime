<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

final readonly class UpdateFieldDto
{
    public function __construct(
        public ?string $fieldName,
        public ?int $dataType,
        public ?bool $isRequired,
        public ?int $maxLength,
    ) {
    }
}

