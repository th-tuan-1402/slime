<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Schema\SchemaRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldSearcher
{
    public function __construct(
        private readonly FieldRepository $fieldRepository,
        private readonly SchemaRepository $schemaRepository,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $schemaId): array
    {
        $this->assertSchemaExists($schemaId);
        return $this->fieldRepository->listBySchema($schemaId);
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $schemaId, int $fieldId): array
    {
        $this->assertSchemaExists($schemaId);
        $field = $this->fieldRepository->findInSchema($schemaId, $fieldId);
        if ($field === null) {
            throw new NotFoundHttpException('Field not found.');
        }

        $config = $this->fieldRepository->findFieldConfig($fieldId);
        $field['config'] = $config;

        return $field;
    }

    private function assertSchemaExists(int $schemaId): void
    {
        $schema = $this->schemaRepository->find($schemaId);
        if ($schema === null) {
            throw new NotFoundHttpException('Schema not found.');
        }
    }
}

