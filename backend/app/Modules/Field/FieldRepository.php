<?php

declare(strict_types=1);

namespace App\Modules\Field;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @return array<string, mixed>|null
     */
    public function findById(int $fieldId): ?array
    {
        $row = $this->db->table('db_field')
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

    /**
     * @return list<array{id:int,value:string,label:string,order:int,isActive:bool}>
     */
    public function listSelections(int $fieldId): array
    {
        /** @var list<array{id:int,value:string,label:string,order:int,isActive:bool}> $rows */
        $rows = $this->db->table('field_selection')
            ->where('field_id', '=', $fieldId)
            ->orderBy('selection_order')
            ->get()
            ->map(static function (object $row): array {
                $data = (array) $row;

                return [
                    'id' => (int) ($data['selection_id'] ?? 0),
                    'value' => (string) ($data['selection_value'] ?? ''),
                    'label' => (string) ($data['selection_label'] ?? ''),
                    'order' => (int) ($data['selection_order'] ?? 0),
                    'isActive' => ((int) ($data['is_active'] ?? 1)) === 1,
                ];
            })
            ->all();

        return $rows;
    }

    /**
     * @param list<array{value:string,label:string,order:int,is_active:bool}> $options
     */
    public function replaceSelections(int $fieldId, array $options, int $actorUserId): void
    {
        $this->db->table('field_selection')->where('field_id', '=', $fieldId)->delete();

        $now = Carbon::now();
        $rows = array_map(
            static fn(array $option): array => [
                'field_id' => $fieldId,
                'selection_value' => $option['value'],
                'selection_label' => $option['label'],
                'selection_order' => $option['order'],
                'is_active' => $option['is_active'] ? 1 : 0,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ],
            $options,
        );

        $this->db->table('field_selection')->insert($rows);
    }

    /**
     * @return array{prefix:?string,padding:int,nextValue:int,step:int,resetPolicy:string}
     */
    public function findSequenceConfig(int $fieldId): array
    {
        $config = $this->findFieldConfig($fieldId);
        if ($config === null) {
            return [
                'prefix' => null,
                'padding' => 1,
                'nextValue' => 1,
                'step' => 1,
                'resetPolicy' => 'none',
            ];
        }

        return [
            'prefix' => array_key_exists('sequence_prefix', $config) ? ($config['sequence_prefix'] !== null ? (string) $config['sequence_prefix'] : null) : null,
            'padding' => (int) ($config['sequence_padding'] ?? 1),
            'nextValue' => (int) ($config['sequence_next_value'] ?? 1),
            'step' => (int) ($config['sequence_step'] ?? 1),
            'resetPolicy' => (string) ($config['sequence_reset_policy'] ?? 'none'),
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    public function upsertSequenceConfig(int $fieldId, array $data): void
    {
        $this->upsertFieldConfig($fieldId, $data);
    }

    public function findLinkTargetSchemaId(int $fieldId): ?int
    {
        $config = $this->findFieldConfig($fieldId);
        if ($config === null) {
            return null;
        }

        $candidates = [
            $config['link_schema_id'] ?? null,
            $config['link_db_schema_id'] ?? null,
            $config['target_schema_id'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                return (int) $candidate;
            }
        }

        return null;
    }

    public function findLinkDisplayFieldId(int $fieldId): ?int
    {
        $config = $this->findFieldConfig($fieldId);
        if ($config === null) {
            return null;
        }

        $candidates = [
            $config['link_display_field_id'] ?? null,
            $config['display_field_id'] ?? null,
            $config['target_display_field_id'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                return (int) $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{items:list<array{id:int,display:string}>,page:int,limit:int,total:int}
     * @param list<int>|null $visibleRecordIds
     */
    public function searchLinkedRecords(
        int $schemaId,
        ?int $displayFieldId,
        string $query,
        int $page,
        int $limit,
        ?array $visibleRecordIds = null,
    ): array {
        $tableName = "record_{$schemaId}";
        if (Schema::connection('tenant')->hasTable($tableName) !== true) {
            throw new NotFoundHttpException('Link target records are not available.');
        }

        $displayColumn = $displayFieldId !== null ? "data_0_{$displayFieldId}" : null;
        $builder = $this->db->table($tableName)->select('record_id');
        if ($displayColumn !== null && Schema::connection('tenant')->hasColumn($tableName, $displayColumn) === true) {
            $builder->addSelect($displayColumn);
            if ($query !== '') {
                $builder->where($displayColumn, 'like', '%' . $query . '%');
            }
        }
        if ($visibleRecordIds !== null) {
            if ($visibleRecordIds === []) {
                /** @var list<array{id:int,display:string}> $emptyItems */
                $emptyItems = [];
                return [
                    'items' => $emptyItems,
                    'page' => $page,
                    'limit' => $limit,
                    'total' => 0,
                ];
            }

            $builder->whereIn('record_id', $visibleRecordIds);
        }

        $total = (int) (clone $builder)->count();
        $offset = ($page - 1) * $limit;

        $rows = $builder
            ->orderBy('record_id')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(static function (object $row) use ($displayColumn): array {
                $data = (array) $row;
                $display = $displayColumn !== null && array_key_exists($displayColumn, $data)
                    ? (string) ($data[$displayColumn] ?? '')
                    : (string) $data['record_id'];

                return [
                    'id' => (int) $data['record_id'],
                    'display' => $display,
                ];
            })
            ->all();

        return [
            'items' => array_values($rows),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ];
    }
}

