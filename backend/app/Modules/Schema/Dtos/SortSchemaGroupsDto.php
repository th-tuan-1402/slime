<?php

declare(strict_types=1);

namespace App\Modules\Schema\Dtos;

final readonly class SortSchemaGroupsDto
{
    /**
     * @param list<int> $groupIds
     */
    public function __construct(
        public array $groupIds,
    ) {
    }
}
