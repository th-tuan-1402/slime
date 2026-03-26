<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

final readonly class UpdateFieldSequenceDto
{
    public function __construct(
        public ?string $prefix,
        public int $padding,
        public int $nextValue,
        public int $step,
        public string $resetPolicy,
    ) {
    }
}

