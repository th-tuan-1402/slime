<?php

declare(strict_types=1);

namespace App\Modules\Record;

use App\Enums\RoleKey;
use App\Http\AbstractApiController;
use App\Modules\Record\Requests\ImportRecordsRequest;
use App\Modules\Record\Requests\ListRecordsRequest;
use App\Modules\Shared\Models\Record\DynamicRecordModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RecordController extends AbstractApiController
{
    /**
     * List records for a schema with pagination and optional sort/filter.
     */
    public function index(int $schemaId, ListRecordsRequest $request): JsonResponse
    {
        $this->authorizeRecordRead();

        $page = (int) ($request->validated('page') ?? 1);
        $perPage = (int) ($request->validated('perPage') ?? 20);
        $sortBy = (string) ($request->validated('sortBy') ?? 'record_id');
        $sortDir = (string) ($request->validated('sortDir') ?? 'desc');
        $q = $request->validated('q');

        $filtersRaw = $request->validated('filters');
        $filters = $this->parseFilters($filtersRaw);

        $query = DynamicRecordModel::forSchema($schemaId)->newQuery();

        if ($q !== null && $q !== '') {
            $this->applyKeyword($query, (string) $q);
        }

        if ($filters !== null) {
            $this->applyFilters($schemaId, $query, $filters);
        }

        [$sortColumn, $direction] = $this->normalizeSort($sortBy, $sortDir);
        $query->orderBy($sortColumn, $direction);

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        return $this->respondPaginated($paginator);
    }

    /**
     * Export filtered records as CSV.
     */
    public function export(int $schemaId, ListRecordsRequest $request): StreamedResponse
    {
        $this->authorizeRecordRead();

        $page = 1;
        $perPage = 1000;
        $sortBy = (string) ($request->validated('sortBy') ?? 'record_id');
        $sortDir = (string) ($request->validated('sortDir') ?? 'desc');
        $q = $request->validated('q');

        $filtersRaw = $request->validated('filters');
        $filters = $this->parseFilters($filtersRaw);

        $query = DynamicRecordModel::forSchema($schemaId)->newQuery();

        if ($q !== null && $q !== '') {
            $this->applyKeyword($query, (string) $q);
        }

        if ($filters !== null) {
            $this->applyFilters($schemaId, $query, $filters);
        }

        [$sortColumn, $direction] = $this->normalizeSort($sortBy, $sortDir);
        $query->orderBy($sortColumn, $direction);

        $filename = "records-schema-{$schemaId}.csv";

        return response()->streamDownload(function () use ($query, $perPage, $page): void {
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                return;
            }

            $headerWritten = false;
            $columns = [];
            $currentPage = $page;

            while (true) {
                $paginator = $query->paginate(perPage: $perPage, page: $currentPage);
                $items = $paginator->items();
                if (!$headerWritten) {
                    $first = $items[0] ?? null;
                    $columns = $first ? array_keys((array) $first) : ['record_id'];
                    fputcsv($out, $columns);
                    $headerWritten = true;
                }

                foreach ($items as $row) {
                    $arr = (array) $row;
                    $line = [];
                    foreach ($columns as $col) {
                        $line[] = $arr[$col] ?? null;
                    }
                    fputcsv($out, $line);
                }

                if ($currentPage >= $paginator->lastPage()) {
                    break;
                }
                $currentPage++;
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Import records from CSV (multipart form-data `file`).
     */
    public function import(int $schemaId, ImportRecordsRequest $request): JsonResponse
    {
        $this->authorizeRecordWrite();

        $file = $request->file('file');
        if ($file === null) {
            return $this->respondError('CSV file is required.', 422);
        }

        $path = $file->getRealPath();
        if ($path === false) {
            return $this->respondError('Failed to read uploaded file.', 400);
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return $this->respondError('Failed to open uploaded file.', 400);
        }

        $header = fgetcsv($handle);
        if ($header === false || $header === []) {
            fclose($handle);
            return $this->respondError('CSV header row is missing.', 400);
        }

        /** @var list<string> $columns */
        $columns = array_values(array_map('strval', $header));
        $allowed = array_values(array_filter($columns, fn (string $col): bool => $this->isAllowedColumn($col)));
        if ($allowed === []) {
            fclose($handle);
            return $this->respondError('No importable columns found in CSV header.', 400);
        }

        $inserted = 0;
        $skipped = 0;

        DB::connection('tenant')->transaction(function () use ($schemaId, $handle, $columns, $allowed, &$inserted, &$skipped): void {
            $model = DynamicRecordModel::forSchema($schemaId);
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $row === []) {
                    continue;
                }
                $payload = [];
                foreach ($allowed as $col) {
                    $index = array_search($col, $columns, true);
                    if ($index === false) {
                        continue;
                    }
                    $payload[$col] = $row[$index] ?? null;
                }

                if ($payload === []) {
                    $skipped++;
                    continue;
                }

                $record = $model->newInstance([], true);
                foreach ($payload as $key => $value) {
                    $record->setAttribute($key, $value);
                }
                $record->save();
                $inserted++;
            }
        });

        fclose($handle);

        return $this->respondSuccess([
            'inserted' => $inserted,
            'skipped' => $skipped,
        ]);
    }

    private function authorizeRecordRead(): void
    {
        $this->authorizeRole(RoleKey::Admin, RoleKey::Manager, RoleKey::Staff, RoleKey::ReadOnly);
    }

    private function authorizeRecordWrite(): void
    {
        $this->authorizeRole(RoleKey::Admin, RoleKey::Manager);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseFilters(mixed $filtersRaw): ?array
    {
        if (!is_string($filtersRaw) || $filtersRaw === '') {
            return null;
        }
        try {
            $parsed = json_decode($filtersRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
        if (!is_array($parsed)) {
            return null;
        }
        return $parsed;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<DynamicRecordModel> $query
     */
    private function applyKeyword($query, string $q): void
    {
        $trim = trim($q);
        if ($trim === '') {
            return;
        }

        if (ctype_digit($trim)) {
            $query->where('record_id', (int) $trim);
            return;
        }

        // Fallback: try matching outer id.
        $query->where('record_outer_id', 'like', '%' . $trim . '%');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<DynamicRecordModel> $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(int $schemaId, $query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if (!is_string($key) || !$this->isAllowedColumn($key)) {
                continue;
            }
            // Optional existence check for dynamic columns to avoid SQL errors.
            if ($this->isDynamicDataColumn($key) && !\Schema::connection('tenant')->hasColumn('record_' . $schemaId, $key)) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $query->where($key, $value);
        }
    }

    /**
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    private function normalizeSort(string $sortBy, string $sortDir): array
    {
        $column = $this->isAllowedColumn($sortBy) ? $sortBy : 'record_id';
        $dir = $sortDir === 'asc' ? 'asc' : 'desc';
        return [$column, $dir];
    }

    private function isAllowedColumn(string $column): bool
    {
        if ($column === 'record_id'
            || $column === 'parent_record_id'
            || $column === 'record_outer_id'
            || $column === 'regist_date'
            || $column === 'update_date'
            || $column === 'regist_user_id'
            || $column === 'update_user_id'
        ) {
            return true;
        }

        return $this->isDynamicDataColumn($column);
    }

    private function isDynamicDataColumn(string $column): bool
    {
        return (bool) preg_match('/^data_\d+_\d+$/', $column);
    }
}

