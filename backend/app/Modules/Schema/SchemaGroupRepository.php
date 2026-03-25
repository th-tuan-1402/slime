<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

final class SchemaGroupRepository
{
    private ConnectionInterface $db;

    public function __construct()
    {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table('db_group')
            ->orderBy('dbg_order')
            ->get()
            ->map(static fn(object $row): array => (array) $row)
            ->all();

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $dbgId): ?array
    {
        $row = $this->db->table('db_group')
            ->where('dbg_id', '=', $dbgId)
            ->first();

        return $row !== null ? (array) $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        /** @var int $id */
        $id = (int) $this->db->table('db_group')->insertGetId($data, 'dbg_id');
        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $dbgId, array $data): int
    {
        return (int) $this->db->table('db_group')
            ->where('dbg_id', '=', $dbgId)
            ->update($data);
    }

    public function delete(int $dbgId): int
    {
        return (int) $this->db->table('db_group')
            ->where('dbg_id', '=', $dbgId)
            ->delete();
    }

    /**
     * @param list<int> $orderedGroupIds
     */
    public function sort(array $orderedGroupIds): void
    {
        foreach ($orderedGroupIds as $index => $groupId) {
            $this->db->table('db_group')
                ->where('dbg_id', '=', $groupId)
                ->update(['dbg_order' => $index + 1]);
        }
    }

    public function getMaxOrder(): int
    {
        /** @var int|null $max */
        $max = $this->db->table('db_group')->max('dbg_order');
        return $max ?? 0;
    }
}

