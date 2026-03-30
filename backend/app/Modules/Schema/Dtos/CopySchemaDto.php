<?php

declare(strict_types=1);

namespace App\Modules\Schema\Dtos;

final readonly class CopySchemaDto
{
    public function __construct(
        public ?string $dbSchemaName,
    ) {
    }
}

