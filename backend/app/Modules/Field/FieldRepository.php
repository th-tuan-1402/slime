<?php

declare(strict_types=1);

namespace App\Modules\Field;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

final class FieldRepository
{
    private ConnectionInterface $db;

    public function __construct()
    {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listBySchema(int $schemaId): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table('db_field')
            ->where('db_schema_id', '=', $schemaId)
            ->orderBy('db_field_order')
            ->get()
            ->map(static fn(object $row): array => (array) $row)
            ->all();

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findInSchema(int $schemaId, int $fieldId): ?array
    {
        $row = $this->db->table('db_field')
            ->where('db_schema_id', '=', $schemaId)
            ->where('field_id', '=', $fieldId)
            ->first();

        return $row !== null ? (array) $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insertField(array $data): int
    {
        /** @var int $id */
        $id = (int) $this->db->table('db_field')->insertGetId($data, 'field_id');
        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateField(int $fieldId, array $data): int
    {
        return (int) $this->db->table('db_field')
            ->where('field_id', '=', $fieldId)
            ->update($data);
    }

    public function deleteField(int $fieldId): int
    {
        return (int) $this->db->table('db_field')
            ->where('field_id', '=', $fieldId)
            ->delete();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function upsertFieldConfig(int $fieldId, array $data): void
    {
        $exists = $this->db->table('field_configs')
            ->where('field_id', '=', $fieldId)
            ->exists();

        if ($exists === true) {
            $this->db->table('field_configs')
                ->where('field_id', '=', $fieldId)
                ->update($data);
            return;
        }

        $data['field_id'] = $fieldId;
        $this->db->table('field_configs')->insert($data);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFieldConfig(int $fieldId): ?array
    {
        $row = $this->db->table('field_configs')
            ->where('field_id', '=', $fieldId)
            ->first();

        return $row !== null ? (array) $row : null;
    }

    public function deleteFieldConfig(int $fieldId): int
    {
        return (int) $this->db->table('field_configs')
            ->where('field_id', '=', $fieldId)
            ->delete();
    }

    public function getMaxOrder(int $schemaId): int
    {
        /** @var int|null $max */
        $max = $this->db->table('db_field')
            ->where('db_schema_id', '=', $schemaId)
            ->max('db_field_order');

        return $max ?? 0;
    }

    /**
     * @param list<int> $orderedFieldIds
     */
    public function sort(int $schemaId, array $orderedFieldIds): void
    {
        foreach ($orderedFieldIds as $index => $fieldId) {
            $this->db->table('db_field')
                ->where('db_schema_id', '=', $schemaId)
                ->where('field_id', '=', $fieldId)
                ->update(['db_field_order' => $index + 1]);
        }
    }
}

