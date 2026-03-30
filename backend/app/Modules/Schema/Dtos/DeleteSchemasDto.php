<?php

declare(strict_types=1);

namespace App\Modules\Schema\Dtos;

final readonly class DeleteSchemasDto
{
    /**
     * @param list<int> $schemaIds
     */
    public function __construct(
        public array $schemaIds,
    ) {
    }
}
