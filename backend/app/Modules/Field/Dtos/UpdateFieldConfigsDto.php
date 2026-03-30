<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

/**
 * @phpstan-type FieldConfigMap array<string, bool|int|string|null>
 */
final readonly class UpdateFieldConfigsDto
{
    /**
     * @param FieldConfigMap $configs
     */
    public function __construct(
        public array $configs,
    ) {
    }
}

