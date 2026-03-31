<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Tenant meta tables needed by /api/v1/schemas, /api/v1/fields and related endpoints.
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
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('field_selection');
        Schema::connection('tenant')->dropIfExists('field_configs');
        Schema::connection('tenant')->dropIfExists('db_field');
        Schema::connection('tenant')->dropIfExists('db_schema');
        Schema::connection('tenant')->dropIfExists('db_group');
    }
};

