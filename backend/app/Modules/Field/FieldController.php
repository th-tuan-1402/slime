<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Enums\RoleKey;
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

    /**
     * List selection options configured for the field.
     * Used for dropdown/radio option retrieval.
     */
    public function selections(int $fieldId): JsonResponse
    {
        $this->authorizeFieldRead();
        return $this->respondSuccess($this->fieldSelectionSearcher->selections($fieldId));
    }

    /**
     * Replace all selection options for a field in one transaction.
     */
    public function updateSelections(int $fieldId, UpdateFieldSelectionsRequest $request): JsonResponse
    {
        $this->authorizeFieldWrite();
        /** @var \App\Modules\Field\Dtos\UpdateFieldSelectionsDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionEditor->updateSelections($fieldId, $dto, $this->currentUserId());
        return $this->respondSuccess($data);
    }

    /**
     * Get sequence configuration for the field.
     */
    public function sequence(int $fieldId): JsonResponse
    {
        $this->authorizeFieldRead();
        return $this->respondSuccess($this->fieldSelectionSearcher->sequence($fieldId));
    }

    /**
     * Update sequence configuration for a field.
     */
    public function updateSequence(int $fieldId, UpdateFieldSequenceRequest $request): JsonResponse
    {
        $this->authorizeFieldWrite();
        /** @var \App\Modules\Field\Dtos\UpdateFieldSequenceDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionEditor->updateSequence($fieldId, $dto, $this->currentUserId());
        return $this->respondSuccess($data);
    }

    /**
     * Search linked records by keyword for a link field.
     */
    public function searchLinks(int $fieldId, SearchFieldLinksRequest $request): JsonResponse
    {
        $this->authorizeFieldRead();
        /** @var \App\Modules\Field\Dtos\SearchFieldLinksDto $dto */
        $dto = $request->toDto();
        $data = $this->fieldSelectionSearcher->searchLinks(
            $fieldId,
            $dto,
            $this->currentVisibleRecordIds(),
        );
        return $this->respondSuccess($data);
    }

    private function authorizeFieldRead(): void
    {
        $this->authorizeRole(RoleKey::Admin, RoleKey::Manager, RoleKey::Staff, RoleKey::ReadOnly);
    }

    private function authorizeFieldWrite(): void
    {
        $this->authorizeRole(RoleKey::Admin, RoleKey::Manager);
    }

    /**
     * @return list<int>|null
     */
    private function currentVisibleRecordIds(): ?array
    {
        $visibleIds = $this->currentUser()->visible_record_ids ?? null;
        if ($visibleIds === null || $visibleIds === '') {
            return null;
        }

        if (is_array($visibleIds)) {
            $raw = $visibleIds;
        } else {
            $raw = explode(',', (string) $visibleIds);
        }

        $ids = [];
        foreach ($raw as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $ids[] = (int) $value;
        }

        return array_values(array_unique($ids));
    }
}

