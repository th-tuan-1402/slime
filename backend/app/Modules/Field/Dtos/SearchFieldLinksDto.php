<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

final readonly class SearchFieldLinksDto
{
    public function __construct(
        public string $query,
        public int $page,
        public int $limit,
    ) {
    }
}

