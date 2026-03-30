<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use App\Modules\Schema\Dtos\CreateSchemaDto;
use App\Modules\Schema\Dtos\UpdateSchemaDto;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Validation\ValidationException;
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

    /**
     * @return array<string, mixed>
     */
    public function deleteConfirm(int $schemaId): array
    {
        $schema = $this->schemaRepository->find($schemaId);
        if ($schema === null) {
            throw new NotFoundHttpException('Schema not found.');
        }

        $recordTable = "record_{$schemaId}";
        $recordTableExists = SchemaFacade::connection('tenant')->hasTable($recordTable);

        $result = [
            'schemaId' => $schemaId,
            'schema' => $schema,
            'affected' => [
                'db_field' => [
                    'exists' => SchemaFacade::connection('tenant')->hasTable('db_field'),
                    'count' => 0,
                ],
                'access_info' => [
                    'exists' => SchemaFacade::connection('tenant')->hasTable('access_info'),
                    'count' => 0,
                ],
                'db_mail_config' => [
                    'exists' => SchemaFacade::connection('tenant')->hasTable('db_mail_config'),
                    'count' => 0,
                ],
                'record_table' => [
                    'name' => $recordTable,
                    'exists' => $recordTableExists,
                    'count' => 0,
                ],
            ],
        ];

        if ($result['affected']['db_field']['exists'] === true) {
            $result['affected']['db_field']['count'] = (int) $this->db->table('db_field')
                ->where('db_schema_id', '=', $schemaId)
                ->count();
        }

        if ($result['affected']['access_info']['exists'] === true) {
            $result['affected']['access_info']['count'] = (int) $this->db->table('access_info')
                ->where('db_schema_id', '=', $schemaId)
                ->count();
        }

        if ($result['affected']['db_mail_config']['exists'] === true) {
            $result['affected']['db_mail_config']['count'] = (int) $this->db->table('db_mail_config')
                ->where('db_schema_id', '=', $schemaId)
                ->count();
        }

        if ($recordTableExists) {
            $result['affected']['record_table']['count'] = (int) $this->db->table($recordTable)->count();
        }

        return $result;
    }

    /**
     * @return array{schemaId:int,deleted:bool}
     */
    public function deleteCascade(int $schemaId, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($schemaId): array {
            $existing = $this->schemaRepository->find($schemaId);
            if ($existing === null) {
                throw new NotFoundHttpException('Schema not found.');
            }

            // B1 scope cascade: db_field + optional access_info/db_mail_config + record_<schemaId>
            if (SchemaFacade::connection('tenant')->hasTable('db_field')) {
                $this->db->table('db_field')->where('db_schema_id', '=', $schemaId)->delete();
            }
            if (SchemaFacade::connection('tenant')->hasTable('access_info')) {
                $this->db->table('access_info')->where('db_schema_id', '=', $schemaId)->delete();
            }
            if (SchemaFacade::connection('tenant')->hasTable('db_mail_config')) {
                $this->db->table('db_mail_config')->where('db_schema_id', '=', $schemaId)->delete();
            }

            $recordTable = "record_{$schemaId}";
            if (SchemaFacade::connection('tenant')->hasTable($recordTable)) {
                SchemaFacade::connection('tenant')->drop($recordTable);
            }

            $deleted = $this->schemaRepository->delete($schemaId);
            if ($deleted !== 1) {
                throw ValidationException::withMessages([
                    'schema_id' => [sprintf('Failed to delete schema_id=%d.', $schemaId)],
                ]);
            }

            return [
                'schemaId' => $schemaId,
                'deleted' => true,
            ];
        });
    }

    /**
     * @param list<int> $schemaIds
     * @return array{summary:array{total:int,deleted:int,failed:int},results:list<array{schemaId:int,deleted:bool,error:?string,errorCode:?string}>}
     */
    public function batchDeleteCascade(array $schemaIds, int $actorUserId): array
    {
        if ($schemaIds === []) {
            throw new ConflictHttpException('schema_ids must not be empty.');
        }

        $results = [];
        $deletedCount = 0;

        foreach ($schemaIds as $schemaId) {
            try {
                $this->deleteCascade($schemaId, $actorUserId);
                $deletedCount++;
                $results[] = [
                    'schemaId' => $schemaId,
                    'deleted' => true,
                    'error' => null,
                    'errorCode' => null,
                ];
            } catch (NotFoundHttpException) {
                $results[] = [
                    'schemaId' => $schemaId,
                    'deleted' => false,
                    'error' => 'Failed.',
                    'errorCode' => 'NOT_FOUND',
                ];
            } catch (ValidationException) {
                $results[] = [
                    'schemaId' => $schemaId,
                    'deleted' => false,
                    'error' => 'Failed.',
                    'errorCode' => 'VALIDATION_FAILED',
                ];
            } catch (\Throwable) {
                $results[] = [
                    'schemaId' => $schemaId,
                    'deleted' => false,
                    'error' => 'Failed.',
                    'errorCode' => 'FAILED',
                ];
            }
        }

        $total = count($schemaIds);
        $failed = $total - $deletedCount;

        return [
            'summary' => [
                'total' => $total,
                'deleted' => $deletedCount,
                'failed' => $failed,
            ],
            'results' => $results,
        ];
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

