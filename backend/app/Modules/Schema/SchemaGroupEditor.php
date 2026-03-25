<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use App\Modules\Schema\Dtos\CreateSchemaGroupDto;
use App\Modules\Schema\Dtos\UpdateSchemaGroupDto;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SchemaGroupEditor
{
    private ConnectionInterface $db;

    public function __construct(
        private readonly SchemaGroupRepository $groupRepository,
        private readonly SchemaRepository $schemaRepository,
    ) {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CreateSchemaGroupDto $dto, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($dto, $actorUserId): array {
            $now = now();
            $order = $this->groupRepository->getMaxOrder() + 1;

            $id = $this->groupRepository->insert([
                'dbg_name' => $dto->dbgName,
                'dbg_comment' => $dto->dbgComment ?? '',
                'dbg_order' => $order,
                'regist_user_id' => $actorUserId,
                'regist_date' => $now,
                'update_user_id' => $actorUserId,
                'update_date' => $now,
            ]);

            $group = $this->groupRepository->find($id);
            if ($group === null) {
                throw new NotFoundHttpException('Group not found after creation.');
            }

            return $group;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function update(int $dbgId, UpdateSchemaGroupDto $dto, int $actorUserId): array
    {
        return $this->db->transaction(function () use ($dbgId, $dto, $actorUserId): array {
            $existing = $this->groupRepository->find($dbgId);
            if ($existing === null) {
                throw new NotFoundHttpException('Group not found.');
            }

            $update = [];
            if ($dto->dbgName !== null) {
                $update['dbg_name'] = $dto->dbgName;
            }
            if ($dto->dbgComment !== null) {
                $update['dbg_comment'] = $dto->dbgComment;
            }

            if ($update === []) {
                return $existing;
            }

            $update['update_user_id'] = $actorUserId;
            $update['update_date'] = now();

            $this->groupRepository->update($dbgId, $update);

            $group = $this->groupRepository->find($dbgId);
            if ($group === null) {
                throw new NotFoundHttpException('Group not found after update.');
            }

            return $group;
        });
    }

    public function delete(int $dbgId, int $actorUserId): void
    {
        $this->db->transaction(function () use ($dbgId, $actorUserId): void {
            $existing = $this->groupRepository->find($dbgId);
            if ($existing === null) {
                throw new NotFoundHttpException('Group not found.');
            }

            // Unlink schemas first (per spec: do not cascade delete schemas)
            $this->schemaRepository->unlinkSchemasFromGroup($dbgId, 0);

            $this->groupRepository->delete($dbgId);
        });
    }

    /**
     * @param list<int> $groupIds
     */
    public function sort(array $groupIds): void
    {
        $this->groupRepository->sort($groupIds);
    }
}

