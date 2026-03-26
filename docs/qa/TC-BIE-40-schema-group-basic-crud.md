# Test Cases: [B1] Schema + Group basic CRUD API

## Ref
- Issue: BIE-40 (`https://github.com/th-tuan-1402/slime/issues/9`)
- Scope: Issue body (DD link not present on GitHub issue; verified routes on branch `feature/BIE-40-schema-group-crud`)

## Test Environment
- Docker Compose: `docker compose up -d`
- Run tests: `docker compose exec -T app php artisan test`
- Notes:
  - Endpoints are protected by `auth:sanctum`
  - Tenant data uses DB connection `tenant` and tables `db_group`, `db_schema`, dynamic `record_<schemaId>`

## Test Cases

### Auth / Access control
#### TC-01: Unauthenticated request returns 401 (schemas)
- **Steps**: `GET /api/v1/schemas` without auth header
- **Expected**: 401
- **Result**: PASS

#### TC-02: Unauthenticated request returns 401 (schema-groups)
- **Steps**: `GET /api/v1/schema-groups` without auth header
- **Expected**: 401
- **Result**: PASS

### Schema Groups
#### TC-03: Create group success
- **Input**: `{ "dbg_name": "Group A", "dbg_comment": "c" }`
- **Steps**: `POST /api/v1/schema-groups` with auth
- **Expected**:
  - 201
  - `success=true`
  - response `data.dbg_id` exists
- **Result**: PASS

#### TC-04: Create group validation (missing name)
- **Steps**: `POST /api/v1/schema-groups` with `{}` with auth
- **Expected**: 422
- **Result**: PASS

#### TC-05: List groups ordered by `dbg_order`
- **Precondition**: Create 2 groups, then call sort group ids reversed
- **Steps**: `GET /api/v1/schema-groups`
- **Expected**: list is sorted by `dbg_order` (after sort)
- **Result**: PASS

#### TC-06: Update group success
- **Precondition**: group exists
- **Steps**: `PUT /api/v1/schema-groups/{id}` with `{ "dbg_name": "Group A2" }`
- **Expected**: 200, updated fields reflected in `data`
- **Result**: PASS

#### TC-07: Show group not found
- **Steps**: `GET /api/v1/schema-groups/999999` with auth
- **Expected**: 404
- **Result**: PASS

#### TC-08: Delete group detaches schemas (no cascade delete)
- **Precondition**: Create group + create schema referencing it (`dbg_id`)
- **Steps**: `DELETE /api/v1/schema-groups/{id}`
- **Expected**:
  - 204
  - schema still exists, but `dbg_id` becomes 0
- **Result**: PASS

### Schemas
#### TC-09: Create schema success + record table created
- **Precondition**: group exists (dbg_id)
- **Input**: `{ "dbg_id": <groupId>, "db_schema_name": "Schema A", "db_schema_comment": "c", "schema_type": 0 }`
- **Steps**: `POST /api/v1/schemas`
- **Expected**:
  - 201
  - `data.db_schema_id` exists
  - tenant DB has table `record_<db_schema_id>`
- **Result**: PASS

#### TC-10: List schemas supports group filter
- **Precondition**: Create 2 groups; create schemas under each
- **Steps**: `GET /api/v1/schemas?group_id=<groupId>`
- **Expected**: only schemas with `dbg_id=<groupId>` returned
- **Result**: PASS

#### TC-11: Show schema not found
- **Steps**: `GET /api/v1/schemas/999999` with auth
- **Expected**: 404
- **Result**: PASS

#### TC-12: Update schema success
- **Precondition**: schema exists
- **Steps**: `PUT /api/v1/schemas/{id}` with `{ "db_schema_name": "Schema A2" }`
- **Expected**: 200, updated fields reflected
- **Result**: PASS

#### TC-13: Sort schemas changes `db_schema_order`
- **Precondition**: Create 2 schemas, call sort with reversed ids
- **Steps**: `PUT /api/v1/schemas/sort` with `{ "schema_ids": [id2, id1] }`
- **Expected**: 200 and subsequent list is ordered by `db_schema_order`
- **Result**: PASS

## Edge Cases

### TC-E01: Sort groups rejects empty list
- **Steps**: `PUT /api/v1/schema-groups/sort` with `{ "group_ids": [] }`
- **Expected**: 422 (request validation)
- **Result**: PASS

### TC-E02: Create schema validation errors
- **Steps**: `POST /api/v1/schemas` missing required fields / invalid `schema_type`
- **Expected**: 422
- **Result**: PASS

## Evidence
- Test file: `backend/tests/Feature/Schema/SchemaGroupAndSchemaApiTest.php`
- Command:
  - `docker compose up -d`
  - `docker compose exec -T app php artisan test`
- Result:

```text
Tests: 10 passed (38 assertions)
```

## Coverage Checklist
- [ ] Auth required on all endpoints
- [ ] Happy path CRUD for group + schema
- [ ] Validation error cases
- [ ] Not found cases
- [ ] Sorting behavior + order persistence
- [ ] Delete group detaches schemas (dbg_id => 0)

