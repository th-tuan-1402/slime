<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use App\Modules\Schema\Dtos\CopySchemaDto;
use App\Modules\Schema\Dtos\CreateSchemaDto;
use App\Modules\Schema\Dtos\UpdateSchemaDto;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SchemaEditor
{
    private ConnectionInterface $db;

    public function __construct(
        private readonly SchemaRepository $schemaRepository,
    ) {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CreateSchemaDto $dto, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($dto, $actorUserId): array {
            $now = now();

            $schemaId = $this->schemaRepository->insert([
                'dbg_id' => $dto->dbgId,
                'parent_db_schema_id' => 0,
                'db_schema_name' => $dto->dbSchemaName,
                'db_schema_comment' => $dto->dbSchemaComment ?? '',
                'schema_type' => $dto->schemaType,
                'tabulation_table_flag' => 0,
                'db_schema_order' => 0,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);

            $this->createRecordTableIfMissing($schemaId);
            $this->insertDefaultsIfTablesExist($schemaId, $actorUserId);

            $schema = $this->schemaRepository->find($schemaId);
            if ($schema === null) {
                throw new NotFoundHttpException('Schema not found after creation.');
            }

            return $schema;
        });
    }

    /**
     * Copy schema metadata + field definitions (B1 scope).
     *
     * @return array<string, mixed>
     */
    public function copy(int $sourceSchemaId, CopySchemaDto $dto, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($sourceSchemaId, $dto, $actorUserId): array {
            $source = $this->schemaRepository->find($sourceSchemaId);
            if ($source === null) {
                throw new NotFoundHttpException('Schema not found.');
            }

            $now = now();
            $newName = $dto->dbSchemaName;
            if ($newName === null || $newName === '') {
                $sourceName = (string) ($source['db_schema_name'] ?? '');
                $newName = trim($sourceName) !== '' ? "{$sourceName} (Copy)" : 'Schema Copy';
            }

            $newSchemaId = $this->schemaRepository->insert([
                'dbg_id' => (int) ($source['dbg_id'] ?? 0),
                'parent_db_schema_id' => 0,
                'db_schema_name' => $newName,
                'db_schema_comment' => (string) ($source['db_schema_comment'] ?? ''),
                'schema_type' => (int) ($source['schema_type'] ?? 0),
                'tabulation_table_flag' => 0,
                'db_schema_order' => 0,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);

            $this->createRecordTableIfMissing($newSchemaId);
            $this->copyFieldsBestEffort($sourceSchemaId, $newSchemaId, $actorUserId);

            $schema = $this->schemaRepository->find($newSchemaId);
            if ($schema === null) {
                throw new NotFoundHttpException('Schema not found after copy.');
            }

            return $schema;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function update(int $schemaId, UpdateSchemaDto $dto, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($schemaId, $dto, $actorUserId): array {
            $existing = $this->schemaRepository->find($schemaId);
            if ($existing === null) {
                throw new NotFoundHttpException('Schema not found.');
            }

            $update = [];
            if ($dto->dbgId !== null) {
                $update['dbg_id'] = $dto->dbgId;
            }
            if ($dto->dbSchemaName !== null) {
                $update['db_schema_name'] = $dto->dbSchemaName;
            }
            if ($dto->dbSchemaComment !== null) {
                $update['db_schema_comment'] = $dto->dbSchemaComment;
            }

            if ($update === []) {
                return $existing;
            }

            $update['update_user_id'] = $actorUserId;
            $update['update_date'] = now();

            $this->schemaRepository->update($schemaId, $update);

            $schema = $this->schemaRepository->find($schemaId);
            if ($schema === null) {
                throw new NotFoundHttpException('Schema not found after update.');
            }

            return $schema;
        });
    }

    /**
     * @param list<int> $schemaIds
     */
    public function sort(array $schemaIds): void
    {
        if ($schemaIds === []) {
            throw new ConflictHttpException('schema_ids must not be empty.');
        }

        $this->schemaRepository->sort($schemaIds);
    }

    private function createRecordTableIfMissing(int $schemaId): void
    {
        $table = "record_{$schemaId}";

        if (SchemaFacade::connection('tenant')->hasTable($table)) {
            return;
        }

        SchemaFacade::connection('tenant')->create($table, static function (Blueprint $blueprint): void {
            $blueprint->bigIncrements('record_id');
            $blueprint->integer('regist_user_id')->nullable();
            $blueprint->timestamp('regist_date')->nullable();
            $blueprint->integer('update_user_id')->nullable();
            $blueprint->timestamp('update_date')->nullable();
        });
    }

    private function insertDefaultsIfTablesExist(int $schemaId, int $actorUserId): void
    {
        $now = now();

        // access_info (best-effort)
        if (SchemaFacade::connection('tenant')->hasTable('access_info')) {
            $this->db->table('access_info')->insert([
                'db_schema_id' => $schemaId,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);
        }

        // db_mail_config (best-effort)
        if (SchemaFacade::connection('tenant')->hasTable('db_mail_config')) {
            $this->db->table('db_mail_config')->insert([
                'db_schema_id' => $schemaId,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);
        }
    }

    private function copyFieldsBestEffort(int $sourceSchemaId, int $newSchemaId, int $actorUserId): void
    {
        if (!SchemaFacade::connection('tenant')->hasTable('db_field')) {
            return;
        }

        $columns = SchemaFacade::connection('tenant')->getColumnListing('db_field');
        $excluded = ['field_id'];
        $copyColumns = array_values(array_diff($columns, $excluded));

        /** @var list<object> $rows */
        $rows = $this->db->table('db_field')
            ->where('db_schema_id', '=', $sourceSchemaId)
            ->get()
            ->all();

        foreach ($rows as $rowObj) {
            $row = (array) $rowObj;
            $insert = [];

            foreach ($copyColumns as $col) {
                if (array_key_exists($col, $row)) {
                    $insert[$col] = $row[$col];
                }
            }

            $insert['db_schema_id'] = $newSchemaId;
            if (in_array('regist_user_id', $copyColumns, true)) {
                $insert['regist_user_id'] = $actorUserId;
            }
            if (in_array('regist_date', $copyColumns, true)) {
                $insert['regist_date'] = now();
            }
            if (in_array('update_user_id', $copyColumns, true)) {
                $insert['update_user_id'] = $actorUserId;
            }
            if (in_array('update_date', $copyColumns, true)) {
                $insert['update_date'] = now();
            }

            $this->db->table('db_field')->insert($insert);
        }
    }
}

