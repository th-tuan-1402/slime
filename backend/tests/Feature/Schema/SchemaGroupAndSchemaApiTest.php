<?php

declare(strict_types=1);

namespace Tests\Feature\Schema;

use App\Shared\TenantMiddleware;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class SchemaGroupAndSchemaApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TenantMiddleware::class);

        // The repo's database.php does not define sqlite by default, but phpunit.xml
        // expects sqlite in-memory. Configure both default + tenant connections here.
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.tenant', config('database.connections.sqlite'));

        DB::purge('sqlite');
        DB::purge('tenant');

        $this->setUpAuthTables();
        $this->setUpTenantTables();
    }

    private function setUpAuthTables(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    private function setUpTenantTables(): void
    {
        Schema::connection('tenant')->dropIfExists('db_schema');
        Schema::connection('tenant')->dropIfExists('db_group');
        Schema::connection('tenant')->dropIfExists('db_field');

        Schema::connection('tenant')->create('db_group', static function (Blueprint $table): void {
            $table->increments('dbg_id');
            $table->string('dbg_name');
            $table->text('dbg_comment')->nullable();
            $table->integer('dbg_order')->default(0);
            $table->integer('regist_user_id')->nullable();
            $table->timestamp('regist_date')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });

        Schema::connection('tenant')->create('db_schema', static function (Blueprint $table): void {
            $table->increments('db_schema_id');
            $table->integer('dbg_id')->default(0);
            $table->integer('parent_db_schema_id')->default(0);
            $table->string('db_schema_name');
            $table->text('db_schema_comment')->nullable();
            $table->integer('schema_type')->default(0);
            $table->integer('tabulation_table_flag')->default(0);
            $table->integer('db_schema_order')->default(0);
            $table->integer('regist_user_id')->nullable();
            $table->timestamp('regist_date')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });

        Schema::connection('tenant')->create('db_field', static function (Blueprint $table): void {
            $table->increments('field_id');
            $table->integer('db_schema_id');
            $table->string('field_name')->default('');
            $table->integer('data_type')->default(0);
            $table->integer('db_field_order')->default(0);
            $table->integer('regist_user_id')->nullable();
            $table->timestamp('regist_date')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });
    }

    private function actingAsSanctumUser(): void
    {
        $user = User::query()->create([
            'name' => 'QA User',
            'email' => 'qa@example.test',
            'password' => 'secret',
        ]);

        Sanctum::actingAs($user);
    }

    public function testSchemas_Unauthenticated_Returns401(): void
    {
        $this->getJson('/api/v1/schemas')->assertStatus(401);
    }

    public function testSchemaGroups_Unauthenticated_Returns401(): void
    {
        $this->getJson('/api/v1/schema-groups')->assertStatus(401);
    }

    public function testSchemaGroupCrudAndSort_HappyPath(): void
    {
        $this->actingAsSanctumUser();

        $createA = $this->postJson('/api/v1/schema-groups', [
            'dbg_name' => 'Group A',
            'dbg_comment' => 'c',
        ]);
        $createA->assertStatus(201);
        $createA->assertJsonPath('success', true);

        $groupAId = (int) $createA->json('data.dbg_id');
        $this->assertGreaterThan(0, $groupAId);

        $createB = $this->postJson('/api/v1/schema-groups', [
            'dbg_name' => 'Group B',
        ])->assertStatus(201);
        $groupBId = (int) $createB->json('data.dbg_id');

        $this->getJson("/api/v1/schema-groups/{$groupAId}")
            ->assertStatus(200)
            ->assertJsonPath('data.dbg_id', $groupAId);

        $this->putJson("/api/v1/schema-groups/{$groupAId}", [
            'dbg_name' => 'Group A2',
        ])->assertStatus(200)->assertJsonPath('data.dbg_name', 'Group A2');

        // Sort: B first, then A
        $this->putJson('/api/v1/schema-groups/sort', [
            'group_ids' => [$groupBId, $groupAId],
        ])->assertStatus(200)->assertJsonPath('success', true);

        $list = $this->getJson('/api/v1/schema-groups')->assertStatus(200);
        $ids = array_map(static fn(array $row): int => (int) $row['dbg_id'], (array) $list->json('data'));
        $this->assertSame([$groupBId, $groupAId], array_slice($ids, 0, 2));
    }

    public function testSchemaGroupStore_MissingName_Returns422(): void
    {
        $this->actingAsSanctumUser();

        $this->postJson('/api/v1/schema-groups', [])->assertStatus(422);
    }

    public function testSchemaCrud_ListFilterSort_AndDeleteGroupDetachesSchemas(): void
    {
        $this->actingAsSanctumUser();

        $groupA = $this->postJson('/api/v1/schema-groups', ['dbg_name' => 'Group A'])->assertStatus(201);
        $groupB = $this->postJson('/api/v1/schema-groups', ['dbg_name' => 'Group B'])->assertStatus(201);
        $groupAId = (int) $groupA->json('data.dbg_id');
        $groupBId = (int) $groupB->json('data.dbg_id');

        $schemaA1 = $this->postJson('/api/v1/schemas', [
            'dbg_id' => $groupAId,
            'db_schema_name' => 'Schema A1',
            'db_schema_comment' => 'c',
            'schema_type' => 0,
        ])->assertStatus(201);
        $schemaA1Id = (int) $schemaA1->json('data.db_schema_id');

        $schemaB1 = $this->postJson('/api/v1/schemas', [
            'dbg_id' => $groupBId,
            'db_schema_name' => 'Schema B1',
        ])->assertStatus(201);
        $schemaB1Id = (int) $schemaB1->json('data.db_schema_id');

        Schema::connection('tenant')->hasTable("record_{$schemaA1Id}");
        $this->assertTrue(Schema::connection('tenant')->hasTable("record_{$schemaA1Id}"));
        $this->assertTrue(Schema::connection('tenant')->hasTable("record_{$schemaB1Id}"));
        $this->assertTrue(Schema::connection('tenant')->hasColumn("record_{$schemaA1Id}", 'record_outer_id'));
        $this->assertTrue(Schema::connection('tenant')->hasColumn("record_{$schemaB1Id}", 'record_outer_id'));

        $this->getJson("/api/v1/schemas?group_id={$groupAId}")
            ->assertStatus(200)
            ->assertJsonPath('data.0.dbg_id', $groupAId);

        $this->putJson("/api/v1/schemas/{$schemaA1Id}", [
            'db_schema_name' => 'Schema A1-2',
        ])->assertStatus(200)->assertJsonPath('data.db_schema_name', 'Schema A1-2');

        // Create another schema in group A for sorting
        $schemaA2 = $this->postJson('/api/v1/schemas', [
            'dbg_id' => $groupAId,
            'db_schema_name' => 'Schema A2',
        ])->assertStatus(201);
        $schemaA2Id = (int) $schemaA2->json('data.db_schema_id');

        $this->putJson('/api/v1/schemas/sort', [
            'schema_ids' => [$schemaA2Id, $schemaA1Id],
        ])->assertStatus(200);

        $listA = $this->getJson("/api/v1/schemas?group_id={$groupAId}")->assertStatus(200);
        $ids = array_map(static fn(array $row): int => (int) $row['db_schema_id'], (array) $listA->json('data'));
        $this->assertSame([$schemaA2Id, $schemaA1Id], array_slice($ids, 0, 2));

        // Delete group A should detach schemas, not delete them.
        $this->deleteJson("/api/v1/schema-groups/{$groupAId}")->assertStatus(204);

        $schemaRow = DB::connection('tenant')->table('db_schema')->where('db_schema_id', '=', $schemaA1Id)->first();
        $this->assertNotNull($schemaRow);
        $this->assertSame(0, (int) $schemaRow->dbg_id);
    }

    public function testSchemaAndGroup_ShowNotFound_Returns404(): void
    {
        $this->actingAsSanctumUser();

        $this->getJson('/api/v1/schema-groups/999999')->assertStatus(404);
        $this->getJson('/api/v1/schemas/999999')->assertStatus(404);
    }

    public function testSortGroups_EmptyList_Returns422(): void
    {
        $this->actingAsSanctumUser();

        $this->putJson('/api/v1/schema-groups/sort', ['group_ids' => []])->assertStatus(422);
    }

    public function testCreateSchema_InvalidSchemaType_Returns422(): void
    {
        $this->actingAsSanctumUser();

        $this->postJson('/api/v1/schemas', [
            'dbg_id' => 0,
            'db_schema_name' => 'Schema X',
            'schema_type' => 999,
        ])->assertStatus(422);
    }

    public function testSchemaCopy_CopiesMetadataAndFields_CreatesRecordTable(): void
    {
        $this->actingAsSanctumUser();

        $source = $this->postJson('/api/v1/schemas', [
            'dbg_id' => 0,
            'db_schema_name' => 'Source Schema',
            'schema_type' => 0,
        ])->assertStatus(201);

        $sourceId = (int) $source->json('data.db_schema_id');
        DB::connection('tenant')->table('db_field')->insert([
            'db_schema_id' => $sourceId,
            'field_name' => 'F1',
        ]);
        DB::connection('tenant')->table('db_field')->insert([
            'db_schema_id' => $sourceId,
            'field_name' => 'F2',
        ]);

        $copied = $this->postJson("/api/v1/schemas/{$sourceId}/copy", [
            'db_schema_name' => 'Copied Schema',
        ])->assertStatus(201);

        $newId = (int) $copied->json('data.db_schema_id');
        $this->assertNotSame($sourceId, $newId);
        $copied->assertJsonPath('data.db_schema_name', 'Copied Schema');

        $this->assertTrue(Schema::connection('tenant')->hasTable("record_{$newId}"));

        $count = (int) DB::connection('tenant')->table('db_field')->where('db_schema_id', '=', $newId)->count();
        $this->assertSame(2, $count);
    }

    public function testSchemaCopy_NotFound_Returns404(): void
    {
        $this->actingAsSanctumUser();

        $this->postJson('/api/v1/schemas/999999/copy', [])->assertStatus(404);
    }
}

