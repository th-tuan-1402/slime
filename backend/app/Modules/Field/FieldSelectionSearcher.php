<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Field\Dtos\SearchFieldLinksDto;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldSelectionSearcher
{
    public function __construct(
        private readonly FieldRepository $fieldRepository,
    ) {
    }

    /**
     * @return array{fieldId:int,options:list<array<string,mixed>>}
     */
    public function selections(int $fieldId): array
    {
        $this->assertFieldExists($fieldId);
        return [
            'fieldId' => $fieldId,
            'options' => $this->fieldRepository->listSelections($fieldId),
        ];
    }

    /**
     * @return array{fieldId:int,config:array<string,mixed>}
     */
    public function sequence(int $fieldId): array
    {
        $this->assertFieldExists($fieldId);
        return [
            'fieldId' => $fieldId,
            'config' => $this->fieldRepository->findSequenceConfig($fieldId),
        ];
    }

    /**
     * @return array{items:list<array{id:int,display:string}>,page:int,limit:int,total:int}
     */
    public function searchLinks(int $fieldId, SearchFieldLinksDto $dto): array
    {
        $this->assertFieldExists($fieldId);

        $targetSchemaId = $this->fieldRepository->findLinkTargetSchemaId($fieldId);
        if ($targetSchemaId === null) {
            throw new NotFoundHttpException('Link target schema is not configured.');
        }

        $displayFieldId = $this->fieldRepository->findLinkDisplayFieldId($fieldId);

        return $this->fieldRepository->searchLinkedRecords(
            schemaId: $targetSchemaId,
            displayFieldId: $displayFieldId,
            query: $dto->query,
            page: $dto->page,
            limit: $dto->limit,
        );
    }

    private function assertFieldExists(int $fieldId): void
    {
        if ($this->fieldRepository->findById($fieldId) === null) {
            throw new NotFoundHttpException('Field not found.');
        }
    }
}

