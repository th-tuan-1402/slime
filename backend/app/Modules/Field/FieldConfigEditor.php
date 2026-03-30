<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Field\Dtos\UpdateFieldConfigsDto;
use App\Modules\Schema\SchemaRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldConfigEditor
{
    private ConnectionInterface $db;

    public function __construct(
        private readonly FieldRepository $fieldRepository,
        private readonly SchemaRepository $schemaRepository,
    ) {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return array{schemaId:int,fieldId:int,configs:array<string, mixed>}
     */
    public function update(int $schemaId, int $fieldId, UpdateFieldConfigsDto $dto, int $actorUserId): array
    {
        $this->assertSchemaAndFieldConsistency($schemaId, $fieldId);

        return $this->db->transaction(function () use ($schemaId, $fieldId, $dto, $actorUserId): array {
            $allowedKeys = $this->fieldRepository->editableConfigKeys();
            $unknownKeys = array_values(array_diff(array_keys($dto->configs), $allowedKeys));
            if ($unknownKeys !== []) {
                throw ValidationException::withMessages([
                    'configs' => [sprintf('Unknown config keys: %s', implode(', ', $unknownKeys))],
                ]);
            }

            $typeErrors = $this->fieldRepository->validateConfigTypes($dto->configs);
            if ($typeErrors !== []) {
                throw ValidationException::withMessages($typeErrors);
            }

            $data = $dto->configs;
            $data['update_user_id'] = $actorUserId;
            $data['update_date'] = now();
            $this->fieldRepository->upsertFieldConfig($fieldId, $data);

            return [
                'schemaId' => $schemaId,
                'fieldId' => $fieldId,
                'configs' => $this->fieldRepository->extractEditableConfigMap(
                    $this->fieldRepository->findFieldConfig($fieldId),
                ),
            ];
        });
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

