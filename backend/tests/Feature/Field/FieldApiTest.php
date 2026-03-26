<?php

declare(strict_types=1);

namespace Tests\Feature\Field;

use App\Models\User;
use App\Shared\TenantMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class FieldApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TenantMiddleware::class);

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
            $table->string('field_name');
            $table->integer('data_type')->default(0);
            $table->integer('db_field_order')->default(0);
            $table->integer('regist_user_id')->nullable();
            $table->timestamp('regist_date')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });

        Schema::connection('tenant')->create('field_configs', static function (Blueprint $table): void {
            $table->increments('config_id');
            $table->integer('field_id');
            $table->integer('is_required')->default(0);
            $table->integer('max_length')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });

        DB::connection('tenant')->table('db_schema')->insert([
            'db_schema_id' => 10,
            'dbg_id' => 1,
            'parent_db_schema_id' => 0,
            'db_schema_name' => 'Schema-10',
            'db_schema_comment' => '',
            'schema_type' => 0,
            'tabulation_table_flag' => 0,
            'db_schema_order' => 1,
        ]);

        Schema::connection('tenant')->create('record_10', static function (Blueprint $table): void {
            $table->bigIncrements('record_id');
        });
    }

    private function actingAsSanctumUser(): void
    {
        $user = User::query()->create([
            'name' => 'Field QA',
            'email' => 'field@example.test',
            'password' => 'secret',
        ]);
        Sanctum::actingAs($user);
    }

    public function testFields_Unauthorized_Returns401(): void
    {
        $this->getJson('/api/v1/schemas/10/fields')->assertStatus(401);
    }

    public function testFieldCrudAndSort_HappyPath(): void
    {
        $this->actingAsSanctumUser();

        $create1 = $this->postJson('/api/v1/schemas/10/fields', [
            'field_name' => 'Name',
            'data_type' => 0,
            'is_required' => true,
            'max_length' => 255,
        ])->assertStatus(201);
        $field1Id = (int) $create1->json('data.field_id');
        $this->assertTrue(Schema::connection('tenant')->hasColumn('record_10', "data_0_{$field1Id}"));

        $create2 = $this->postJson('/api/v1/schemas/10/fields', [
            'field_name' => 'Code',
            'data_type' => 0,
        ])->assertStatus(201);
        $field2Id = (int) $create2->json('data.field_id');

        $this->getJson('/api/v1/schemas/10/fields')
            ->assertStatus(200)
            ->assertJsonPath('data.0.field_id', $field1Id);

        $this->getJson("/api/v1/schemas/10/fields/{$field1Id}")
            ->assertStatus(200)
            ->assertJsonPath('data.field_name', 'Name');

        $this->putJson("/api/v1/schemas/10/fields/{$field1Id}", [
            'field_name' => 'Name2',
            'is_required' => false,
        ])->assertStatus(200)->assertJsonPath('data.field_name', 'Name2');

        $this->putJson('/api/v1/schemas/10/fields/sort', [
            'field_ids' => [$field2Id, $field1Id],
        ])->assertStatus(200);

        $list = $this->getJson('/api/v1/schemas/10/fields')->assertStatus(200);
        $ids = array_map(static fn(array $row): int => (int) $row['field_id'], (array) $list->json('data'));
        $this->assertSame([$field2Id, $field1Id], array_slice($ids, 0, 2));

        $this->deleteJson("/api/v1/schemas/10/fields/{$field1Id}")->assertStatus(204);
        $this->assertFalse(Schema::connection('tenant')->hasColumn('record_10', "data_0_{$field1Id}"));
    }

    public function testFieldEndpoints_NotFoundAndValidation(): void
    {
        $this->actingAsSanctumUser();

        $this->getJson('/api/v1/schemas/999/fields')->assertStatus(404);
        $this->getJson('/api/v1/schemas/10/fields/999')->assertStatus(404);
        $this->postJson('/api/v1/schemas/10/fields', [])->assertStatus(422);
        $this->putJson('/api/v1/schemas/10/fields/sort', ['field_ids' => []])->assertStatus(422);
    }
}

