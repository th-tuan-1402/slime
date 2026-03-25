<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use App\Http\AbstractApiController;
use App\Modules\Schema\Requests\SortSchemaGroupsRequest;
use App\Modules\Schema\Requests\StoreSchemaGroupRequest;
use App\Modules\Schema\Requests\UpdateSchemaGroupRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SchemaGroupController extends AbstractApiController
{
    public function __construct(
        private readonly SchemaGroupRepository $groupRepository,
        private readonly SchemaGroupEditor $groupEditor,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->respondSuccess($this->groupRepository->list());
    }

    public function store(StoreSchemaGroupRequest $request): JsonResponse
    {
        /** @var \App\Modules\Schema\Dtos\CreateSchemaGroupDto $dto */
        $dto = $request->toDto();

        $group = $this->groupEditor->create($dto, $this->currentUserId());

        return $this->respondCreated($group);
    }

    public function show(int $id): JsonResponse
    {
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw new NotFoundHttpException('Group not found.');
        }

        return $this->respondSuccess($group);
    }

    public function update(int $id, UpdateSchemaGroupRequest $request): JsonResponse
    {
        /** @var \App\Modules\Schema\Dtos\UpdateSchemaGroupDto $dto */
        $dto = $request->toDto();

        $group = $this->groupEditor->update($id, $dto, $this->currentUserId());

        return $this->respondSuccess($group);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->groupEditor->delete($id);

        return $this->respondNoContent();
    }

    public function sort(SortSchemaGroupsRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        $groupIds = $dto->groupIds;

        $this->groupEditor->sort($groupIds);

        return $this->respondSuccess(null, 'OK');
    }
}

