<?php

declare(strict_types=1);

namespace App\Modules\Schema;

use App\Http\AbstractApiController;
use App\Modules\Schema\Requests\SortSchemasRequest;
use App\Modules\Schema\Requests\StoreSchemaRequest;
use App\Modules\Schema\Requests\UpdateSchemaRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SchemaController extends AbstractApiController
{
    public function __construct(
        private readonly SchemaRepository $schemaRepository,
        private readonly SchemaEditor $schemaEditor,
    ) {
    }

    public function index(): JsonResponse
    {
        $dbgId = request()->query('group_id');
        $dbgIdInt = $dbgId !== null ? (int) $dbgId : null;

        $items = $this->schemaRepository->list($dbgIdInt);

        return $this->respondSuccess($items);
    }

    public function store(StoreSchemaRequest $request): JsonResponse
    {
        /** @var \App\Modules\Schema\Dtos\CreateSchemaDto $dto */
        $dto = $request->toDto();

        $schema = $this->schemaEditor->create($dto, $this->currentUserId());

        return $this->respondCreated($schema);
    }

    public function show(int $id): JsonResponse
    {
        $schema = $this->schemaRepository->find($id);
        if ($schema === null) {
            throw new NotFoundHttpException('Schema not found.');
        }

        return $this->respondSuccess($schema);
    }

    public function update(int $id, UpdateSchemaRequest $request): JsonResponse
    {
        /** @var \App\Modules\Schema\Dtos\UpdateSchemaDto $dto */
        $dto = $request->toDto();

        $schema = $this->schemaEditor->update($id, $dto, $this->currentUserId());

        return $this->respondSuccess($schema);
    }

    public function sort(SortSchemasRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        $schemaIds = $dto->schemaIds;

        $this->schemaEditor->sort($schemaIds);

        return $this->respondSuccess(null, 'OK');
    }
}

