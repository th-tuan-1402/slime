<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Field\Dtos\CreateFieldDto;
use App\Modules\Field\Dtos\UpdateFieldDto;
use App\Modules\Schema\SchemaRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldEditor
{
    private ConnectionInterface $db;

    public function __construct(
        private readonly FieldRepository $fieldRepository,
        private readonly SchemaRepository $schemaRepository,
    ) {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return array<string, mixed>
     */
    public function create(int $schemaId, CreateFieldDto $dto, int $actorUserId): array
    {
        $this->assertSchemaExists($schemaId);

        return $this->db->transaction(function () use ($schemaId, $dto, $actorUserId): array {
            $now = now();
            $order = $this->fieldRepository->getMaxOrder($schemaId) + 1;

            $fieldId = $this->fieldRepository->insertField([
                'db_schema_id' => $schemaId,
                'field_name' => $dto->fieldName,
                'data_type' => $dto->dataType,
                'db_field_order' => $order,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);

            $this->fieldRepository->upsertFieldConfig($fieldId, [
                'is_required' => $dto->isRequired ? 1 : 0,
                'max_length' => $dto->maxLength,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);

            $this->createRecordColumnIfMissing($schemaId, $fieldId);

            $field = $this->fieldRepository->findInSchema($schemaId, $fieldId);
            if ($field === null) {
                throw new NotFoundHttpException('Field not found after creation.');
            }
            $field['config'] = $this->fieldRepository->findFieldConfig($fieldId);

            return $field;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function update(int $schemaId, int $fieldId, UpdateFieldDto $dto, int $actorUserId): array
    {
        $this->assertSchemaExists($schemaId);

        return $this->db->transaction(function () use ($schemaId, $fieldId, $dto, $actorUserId): array {
            $field = $this->fieldRepository->findInSchema($schemaId, $fieldId);
            if ($field === null) {
                throw new NotFoundHttpException('Field not found.');
            }

            $updateField = [];
            if ($dto->fieldName !== null) {
                $updateField['field_name'] = $dto->fieldName;
            }
            if ($dto->dataType !== null) {
                $updateField['data_type'] = $dto->dataType;
            }
            if ($updateField !== []) {
                $updateField['update_user_id'] = $actorUserId;
                $updateField['update_date'] = now();
                $this->fieldRepository->updateField($fieldId, $updateField);
            }

            $updateConfig = [];
            if ($dto->isRequired !== null) {
                $updateConfig['is_required'] = $dto->isRequired ? 1 : 0;
            }
            if ($dto->maxLength !== null) {
                $updateConfig['max_length'] = $dto->maxLength;
            }
            if ($updateConfig !== []) {
                $updateConfig['update_user_id'] = $actorUserId;
                $updateConfig['update_date'] = now();
                $this->fieldRepository->upsertFieldConfig($fieldId, $updateConfig);
            }

            $updated = $this->fieldRepository->findInSchema($schemaId, $fieldId);
            if ($updated === null) {
                throw new NotFoundHttpException('Field not found after update.');
            }
            $updated['config'] = $this->fieldRepository->findFieldConfig($fieldId);

            return $updated;
        });
    }

    public function delete(int $schemaId, int $fieldId): void
    {
        $this->assertSchemaExists($schemaId);

        $this->db->transaction(function () use ($schemaId, $fieldId): void {
            $field = $this->fieldRepository->findInSchema($schemaId, $fieldId);
            if ($field === null) {
                throw new NotFoundHttpException('Field not found.');
            }

            $this->fieldRepository->deleteFieldConfig($fieldId);
            $this->fieldRepository->deleteField($fieldId);
            $this->dropRecordColumnIfExists($schemaId, $fieldId);
        });
    }

    /**
     * @param list<int> $fieldIds
     */
    public function sort(int $schemaId, array $fieldIds): void
    {
        $this->assertSchemaExists($schemaId);
        $this->fieldRepository->sort($schemaId, $fieldIds);
    }

    private function assertSchemaExists(int $schemaId): void
    {
        if ($this->schemaRepository->find($schemaId) === null) {
            throw new NotFoundHttpException('Schema not found.');
        }
    }

    private function createRecordColumnIfMissing(int $schemaId, int $fieldId): void
    {
        $tableName = "record_{$schemaId}";
        $column = "data_0_{$fieldId}";
        if (SchemaFacade::connection('tenant')->hasTable($tableName) !== true) {
            return;
        }
        if (SchemaFacade::connection('tenant')->hasColumn($tableName, $column) === true) {
            return;
        }

        SchemaFacade::connection('tenant')->table($tableName, static function (Blueprint $table) use ($column): void {
            $table->text($column)->nullable();
        });
    }

    private function dropRecordColumnIfExists(int $schemaId, int $fieldId): void
    {
        $tableName = "record_{$schemaId}";
        $column = "data_0_{$fieldId}";
        if (SchemaFacade::connection('tenant')->hasTable($tableName) !== true) {
            return;
        }
        if (SchemaFacade::connection('tenant')->hasColumn($tableName, $column) !== true) {
            return;
        }

        SchemaFacade::connection('tenant')->table($tableName, static function (Blueprint $table) use ($column): void {
            $table->dropColumn($column);
        });
    }
}

