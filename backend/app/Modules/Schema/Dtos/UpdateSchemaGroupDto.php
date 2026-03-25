<?php

declare(strict_types=1);

namespace App\Modules\Schema\Dtos;

final readonly class UpdateSchemaGroupDto
{
    public function __construct(
        public ?string $dbgName,
        public ?string $dbgComment,
    ) {
    }
}

