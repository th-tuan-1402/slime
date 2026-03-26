<?php

declare(strict_types=1);

namespace App\Modules\Field;

use App\Modules\Field\Dtos\UpdateFieldSelectionsDto;
use App\Modules\Field\Dtos\UpdateFieldSequenceDto;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FieldSelectionEditor
{
    private ConnectionInterface $db;

    public function __construct(
        private readonly FieldRepository $fieldRepository,
    ) {
        $this->db = DB::connection('tenant');
    }

    /**
     * @return array{fieldId:int,options:list<array<string,mixed>>}
     */
    public function updateSelections(int $fieldId, UpdateFieldSelectionsDto $dto, int $actorUserId): array
    {
        $this->assertFieldExists($fieldId);

        return $this->db->transaction(function () use ($fieldId, $dto, $actorUserId): array {
            $this->fieldRepository->replaceSelections($fieldId, $dto->options, $actorUserId);

            return [
                'fieldId' => $fieldId,
                'options' => $this->fieldRepository->listSelections($fieldId),
            ];
        });
    }

    /**
     * @return array{fieldId:int,config:array<string,mixed>}
     */
    public function updateSequence(int $fieldId, UpdateFieldSequenceDto $dto, int $actorUserId): array
    {
        $this->assertFieldExists($fieldId);

        return $this->db->transaction(function () use ($fieldId, $dto, $actorUserId): array {
            $this->fieldRepository->upsertSequenceConfig($fieldId, [
                'sequence_prefix' => $dto->prefix,
                'sequence_padding' => $dto->padding,
                'sequence_next_value' => $dto->nextValue,
                'sequence_step' => $dto->step,
                'sequence_reset_policy' => $dto->resetPolicy,
                'update_user_id' => $actorUserId,
                'update_date' => now(),
            ]);

            return [
                'fieldId' => $fieldId,
                'config' => $this->fieldRepository->findSequenceConfig($fieldId),
            ];
        });
    }

    private function assertFieldExists(int $fieldId): void
    {
        if ($this->fieldRepository->findById($fieldId) === null) {
            throw new NotFoundHttpException('Field not found.');
        }
    }
}

