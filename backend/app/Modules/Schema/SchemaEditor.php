<?php

declare(strict_types=1);

namespace App\Modules\Schema;

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
}

