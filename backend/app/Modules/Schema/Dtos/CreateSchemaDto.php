<?php

declare(strict_types=1);

namespace App\Modules\Schema\Dtos;

final readonly class CreateSchemaDto
{
    public function __construct(
        public int $dbgId,
        public string $dbSchemaName,
        public ?string $dbSchemaComment,
        public int $schemaType,
    ) {
    }
}

