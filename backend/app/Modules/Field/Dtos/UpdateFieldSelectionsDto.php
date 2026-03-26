<?php

declare(strict_types=1);

namespace App\Modules\Field\Dtos;

final readonly class UpdateFieldSelectionsDto
{
    /**
     * @param list<array{value:string,label:string,order:int,is_active:bool}> $options
     */
    public function __construct(
        public array $options,
    ) {
    }
}

