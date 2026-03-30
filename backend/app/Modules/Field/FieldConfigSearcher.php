<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Schema\SchemaRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldConfigSearcher
{
    public function __construct(
        private readonly FieldRepository $fieldRepository,
        private readonly SchemaRepository $schemaRepository,
    ) {
    }

    /**
     * @return array{schemaId:int,fieldId:int,configs:array<string, mixed>}
     */
    public function find(int $schemaId, int $fieldId): array
    {
        $this->assertSchemaAndFieldConsistency($schemaId, $fieldId);
        $configs = $this->fieldRepository->findFieldConfig($fieldId);

        return [
            'schemaId' => $schemaId,
            'fieldId' => $fieldId,
            'configs' => $this->fieldRepository->extractEditableConfigMap($configs),
        ];
    }

    private function assertSchemaAndFieldConsistency(int $schemaId, int $fieldId): void
    {
        if ($this->schemaRepository->find($schemaId) === null) {
            throw new NotFoundHttpException('Schema not found.');
        }

        if ($this->fieldRepository->findInSchema($schemaId, $fieldId) === null) {
            throw new NotFoundHttpException('Field not found in schema.');
        }
    }
}

