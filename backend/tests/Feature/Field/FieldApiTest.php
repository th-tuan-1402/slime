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
            $table->string('role')->nullable();
            $table->string('visible_record_ids')->nullable();
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
            $table->string('sequence_prefix')->nullable();
            $table->integer('sequence_padding')->default(1);
            $table->integer('sequence_next_value')->default(1);
            $table->integer('sequence_step')->default(1);
            $table->string('sequence_reset_policy')->default('none');
            $table->integer('link_schema_id')->nullable();
            $table->integer('link_display_field_id')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->timestamp('update_date')->nullable();
        });

        Schema::connection('tenant')->create('field_selection', static function (Blueprint $table): void {
            $table->increments('selection_id');
            $table->integer('field_id');
            $table->string('selection_value');
            $table->string('selection_label');
            $table->integer('selection_order')->default(0);
            $table->integer('is_active')->default(1);
            $table->integer('regist_user_id')->nullable();
            $table->timestamp('regist_date')->nullable();
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

        Schema::connection('tenant')->create('record_20', static function (Blueprint $table): void {
            $table->bigIncrements('record_id');
            $table->text('data_0_2001')->nullable();
        });

        DB::connection('tenant')->table('record_20')->insert([
            ['record_id' => 1, 'data_0_2001' => 'Alpha Customer'],
            ['record_id' => 2, 'data_0_2001' => 'Beta Vendor'],
            ['record_id' => 3, 'data_0_2001' => 'Alpha Partner'],
        ]);
    }

    private function actingAsSanctumUser(string $role = 'admin', ?string $visibleRecordIds = null): void
    {
        $user = User::query()->create([
            'name' => 'Field QA',
            'email' => sprintf('field+%s@example.test', uniqid('', true)),
            'password' => 'secret',
            'role' => $role,
            'visible_record_ids' => $visibleRecordIds,
        ]);
        Sanctum::actingAs($user);
    }

    public function testFields_Unauthorized_Returns401(): void
    {
        $this->getJson('/api/v1/schemas/10/fields')->assertStatus(401);
        $this->getJson('/api/v1/fields/1/selections')->assertStatus(401);
        $this->getJson('/api/v1/fields/1/sequences')->assertStatus(401);
        $this->getJson('/api/v1/fields/1/links/search')->assertStatus(401);
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

    public function testSelectionSequenceAndLinkEndpoints_HappyPath(): void
    {
        $this->actingAsSanctumUser();

        $selectionFieldId = $this->createField('Status', 8);
        $sequenceFieldId = $this->createField('Invoice No', 5);
        $linkFieldId = $this->createField('Customer Link', 4);

        DB::connection('tenant')->table('field_configs')
            ->where('field_id', '=', $linkFieldId)
            ->update([
                'link_schema_id' => 20,
                'link_display_field_id' => 2001,
            ]);

        $this->putJson("/api/v1/fields/{$selectionFieldId}/selections", [
            'options' => [
                ['value' => 'open', 'label' => 'Open', 'order' => 0, 'is_active' => true],
                ['value' => 'close', 'label' => 'Closed', 'order' => 1, 'is_active' => false],
            ],
        ])->assertStatus(200)->assertJsonPath('data.options.0.value', 'open');

        $this->getJson("/api/v1/fields/{$selectionFieldId}/selections")
            ->assertStatus(200)
            ->assertJsonPath('data.options.1.label', 'Closed');

        $this->putJson("/api/v1/fields/{$sequenceFieldId}/sequences", [
            'prefix' => 'INV',
            'padding' => 6,
            'next_value' => 101,
            'step' => 2,
            'reset_policy' => 'monthly',
        ])->assertStatus(200)->assertJsonPath('data.config.padding', 6);

        $this->getJson("/api/v1/fields/{$sequenceFieldId}/sequences")
            ->assertStatus(200)
            ->assertJsonPath('data.config.resetPolicy', 'monthly');

        $this->getJson("/api/v1/fields/{$linkFieldId}/links/search?q=Alpha&page=1&limit=10")
            ->assertStatus(200)
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.items.0.display', 'Alpha Customer');
    }

    public function testSelectionSequenceAndLinkEndpoints_ValidationAndNotFound(): void
    {
        $this->actingAsSanctumUser();
        $fieldId = $this->createField('Validation Field', 8);

        $this->putJson("/api/v1/fields/{$fieldId}/selections", [
            'options' => [
                ['value' => 'same', 'label' => 'A', 'order' => 0],
                ['value' => 'same', 'label' => 'B', 'order' => 1],
            ],
        ])->assertStatus(422);

        $this->putJson("/api/v1/fields/{$fieldId}/sequences", [
            'padding' => 0,
            'next_value' => 0,
            'step' => 0,
            'reset_policy' => 'invalid',
        ])->assertStatus(422);

        $this->getJson('/api/v1/fields/999999/selections')->assertStatus(404);
        $this->getJson('/api/v1/fields/999999/sequences')->assertStatus(404);
        $this->getJson('/api/v1/fields/999999/links/search')->assertStatus(404);
    }

    public function testSelectionSequenceAndLinkEndpoints_FieldTypeMismatch_Returns422(): void
    {
        $this->actingAsSanctumUser();

        $textFieldId = $this->createField('Plain Text', 0);
        $selectionFieldId = $this->createField('Status', 8);
        $linkFieldId = $this->createField('Customer Link', 4);

        DB::connection('tenant')->table('field_configs')
            ->where('field_id', '=', $linkFieldId)
            ->update([
                'link_schema_id' => 20,
                'link_display_field_id' => 2001,
            ]);

        $this->getJson("/api/v1/fields/{$textFieldId}/selections")->assertStatus(422);
        $this->putJson("/api/v1/fields/{$textFieldId}/selections", ['options' => []])->assertStatus(422);
        $this->getJson("/api/v1/fields/{$selectionFieldId}/sequences")->assertStatus(422);
        $this->putJson("/api/v1/fields/{$selectionFieldId}/sequences", [
            'padding' => 2,
            'next_value' => 1,
            'step' => 1,
            'reset_policy' => 'none',
        ])->assertStatus(422);
        $this->getJson("/api/v1/fields/{$selectionFieldId}/links/search")->assertStatus(422);
    }

    public function testSelectionSequenceAndLinkEndpoints_AuthorizationMatrix(): void
    {
        $this->actingAsSanctumUser();
        $selectionFieldId = $this->createField('Status', 8);
        $sequenceFieldId = $this->createField('Invoice No', 5);
        $linkFieldId = $this->createField('Customer Link', 4);

        DB::connection('tenant')->table('field_configs')
            ->where('field_id', '=', $linkFieldId)
            ->update([
                'link_schema_id' => 20,
                'link_display_field_id' => 2001,
            ]);

        $this->actingAsSanctumUser('readonly', '1,3');

        $this->getJson("/api/v1/fields/{$selectionFieldId}/selections")->assertStatus(200);
        $this->getJson("/api/v1/fields/{$sequenceFieldId}/sequences")->assertStatus(200);
        $this->putJson("/api/v1/fields/{$selectionFieldId}/selections", [
            'options' => [['value' => 'open', 'label' => 'Open', 'order' => 0, 'is_active' => true]],
        ])->assertStatus(403);
        $this->putJson("/api/v1/fields/{$sequenceFieldId}/sequences", [
            'padding' => 6,
            'next_value' => 101,
            'step' => 2,
            'reset_policy' => 'monthly',
        ])->assertStatus(403);

        $this->getJson("/api/v1/fields/{$linkFieldId}/links/search?q=Alpha&page=1&limit=10")
            ->assertStatus(200)
            ->assertJsonPath('data.total', 2)
            ->assertJsonMissing(['id' => 2]);
    }

    private function createField(string $name, int $dataType = 0): int
    {
        $create = $this->postJson('/api/v1/schemas/10/fields', [
            'field_name' => $name,
            'data_type' => $dataType,
        ])->assertStatus(201);

        return (int) $create->json('data.field_id');
    }
}

