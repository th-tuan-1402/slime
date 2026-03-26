<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Http\AbstractApiController;
use App\Modules\Field\Requests\SortFieldsRequest;
use App\Modules\Field\Requests\SearchFieldLinksRequest;
use App\Modules\Field\Requests\StoreFieldRequest;
use App\Modules\Field\Requests\UpdateFieldSelectionsRequest;
use App\Modules\Field\Requests\UpdateFieldSequenceRequest;
use App\Modules\Field\Requests\UpdateFieldRequest;
use Illuminate\Http\JsonResponse;

final class FieldController extends AbstractApiController
{
    public function __construct(
        private readonly FieldSearcher $fieldSearcher,
        private readonly FieldEditor $fieldEditor,
        private readonly FieldSelectionSearcher $fieldSelectionSearcher,
        private readonly FieldSelectionEditor $fieldSelectionEditor,
    ) {
    }

    /**
     * List fields in a schema.
     */
    public function index(int $schemaId): JsonResponse
    {
        return $this->respondSuccess($this->fieldSearcher->list($schemaId));
    }

    /**
     * Create field in a schema.
     */
    public function store(int $schemaId, StoreFieldRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\CreateFieldDto $dto */
        $dto = $request->toDto();
        $field = $this->fieldEditor->create($schemaId, $dto, $this->currentUserId());

        return $this->respondCreated($field);
    }

    /**
     * Show field detail.
     */
    public function show(int $schemaId, int $fieldId): JsonResponse
    {
        return $this->respondSuccess($this->fieldSearcher->find($schemaId, $fieldId));
    }

    /**
     * Update field.
     */
    public function update(int $schemaId, int $fieldId, UpdateFieldRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\UpdateFieldDto $dto */
        $dto = $request->toDto();
        $field = $this->fieldEditor->update($schemaId, $fieldId, $dto, $this->currentUserId());

        return $this->respondSuccess($field);
    }

    /**
     * Delete field.
     */
    public function destroy(int $schemaId, int $fieldId): JsonResponse
    {
        $this->fieldEditor->delete($schemaId, $fieldId);
        return $this->respondNoContent();
    }

    /**
     * Sort fields in schema.
     */
    public function sort(int $schemaId, SortFieldsRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\SortFieldsDto $dto */
        $dto = $request->toDto();
        $this->fieldEditor->sort($schemaId, $dto->fieldIds);

        return $this->respondSuccess(null, 'OK');
    }

    public function selections(int $fieldId): JsonResponse
    {
        return $this->respondSuccess($this->fieldSelectionSearcher->selections($fieldId));
    }

    public function updateSelections(int $fieldId, UpdateFieldSelectionsRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\UpdateFieldSelectionsDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionEditor->updateSelections($fieldId, $dto, $this->currentUserId());
        return $this->respondSuccess($data);
    }

    public function sequence(int $fieldId): JsonResponse
    {
        return $this->respondSuccess($this->fieldSelectionSearcher->sequence($fieldId));
    }

    public function updateSequence(int $fieldId, UpdateFieldSequenceRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\UpdateFieldSequenceDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionEditor->updateSequence($fieldId, $dto, $this->currentUserId());
        return $this->respondSuccess($data);
    }

    public function searchLinks(int $fieldId, SearchFieldLinksRequest $request): JsonResponse
    {
        /** @var \App\Modules\Field\Dtos\SearchFieldLinksDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionSearcher->searchLinks($fieldId, $dto);
        return $this->respondSuccess($data);
    }
}

