<?php

declare(strict_types=1);

namespace App\Modules\Field\Requests;

use App\Http\BaseFormRequest;
use App\Modules\Field\Dtos\SearchFieldLinksDto;

final class SearchFieldLinksRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): object
    {
        /** @var array{q?:string,page?:int,limit?:int} $data */
        $data = $this->validated();

        return new SearchFieldLinksDto(
            query: (string) ($data['q'] ?? ''),
            page: (int) ($data['page'] ?? 1),
            limit: (int) ($data['limit'] ?? 20),
        );
    }
}

