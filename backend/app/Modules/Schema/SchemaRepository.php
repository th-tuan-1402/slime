<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

final class SchemaRepository
{
    private ConnectionInterface $db;

    public function __construct()
    {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?int $dbgId): array
    {
        $query = $this->db->table('db_schema')
            ->where('parent_db_schema_id', '=', 0)
            ->where('tabulation_table_flag', '!=', 1)
            ->orderBy('db_schema_order');

        if ($dbgId !== null) {
            $query->where('dbg_id', '=', $dbgId);
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $query->get()->map(static fn(object $row): array => (array) $row)->all();

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $schemaId): ?array
    {
        $row = $this->db->table('db_schema')
            ->where('db_schema_id', '=', $schemaId)
            ->first();

        return $row !== null ? (array) $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        /** @var int $id */
        $id = (int) $this->db->table('db_schema')->insertGetId($data, 'db_schema_id');
        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $schemaId, array $data): int
    {
        return (int) $this->db->table('db_schema')
            ->where('db_schema_id', '=', $schemaId)
            ->update($data);
    }

    /**
     * @param list<int> $orderedSchemaIds
     */
    public function sort(array $orderedSchemaIds): void
    {
        foreach ($orderedSchemaIds as $index => $schemaId) {
            $this->db->table('db_schema')
                ->where('db_schema_id', '=', $schemaId)
                ->update(['db_schema_order' => $index + 1]);
        }
    }

    public function unlinkSchemasFromGroup(int $dbgId, int $defaultDbgId = 0): int
    {
        return (int) $this->db->table('db_schema')
            ->where('dbg_id', '=', $dbgId)
            ->update(['dbg_id' => $defaultDbgId]);
    }
}

