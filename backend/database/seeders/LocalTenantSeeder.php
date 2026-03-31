<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Auth\PasswordHasher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

final class LocalTenantSeeder extends Seeder
{
    private const TENANT_ID_PATTERN = '/^[23456789abcdefghjkmnpqrstuvwxyz]{7}$/';
    private const TENANT_DB_PREFIX = 'ht_';

    public function run(): void
    {
        // Only bootstrap tenant DB in local/dev environments.
        if (!app()->environment(['local', 'development'])) {
            return;
        }

        // If the app isn't using Postgres, skip (e.g. tests).
        if (config('database.connections.pgsql.driver') !== 'pgsql') {
            return;
        }

        $tenantId = (string) (env('LOCAL_TENANT_ID') ?: '2345678');
        if (!preg_match(self::TENANT_ID_PATTERN, $tenantId)) {
            // Invalid tenant id would be rejected by middleware anyway; avoid creating arbitrary DB names.
            return;
        }

        $dbName = self::TENANT_DB_PREFIX . $tenantId;

        $this->ensureDatabaseExists($dbName);
        $this->connectTenantDatabase($dbName);
        $this->ensureTenantTablesExist();
        $this->seedTenantAuthTestAccount();
    }

    private function ensureDatabaseExists(string $dbName): void
    {
        $exists = DB::connection('pgsql')->selectOne(
            'select 1 as ok from pg_database where datname = ? limit 1',
            [$dbName],
        );

        if ($exists !== null) {
            return;
        }

        // CREATE DATABASE cannot run inside a transaction.
        DB::connection('pgsql')->unprepared('CREATE DATABASE "' . str_replace('"', '""', $dbName) . '"');
    }

    private function connectTenantDatabase(string $dbName): void
    {
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function ensureTenantTablesExist(): void
    {
        $this->ensureTenantAuthTablesExist();

        if (!Schema::connection('tenant')->hasTable('db_group')) {
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
        }

        if (!Schema::connection('tenant')->hasTable('db_schema')) {
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
        }

        if (!Schema::connection('tenant')->hasTable('db_field')) {
            Schema::connection('tenant')->create('db_field', static function (Blueprint $table): void {
                $table->increments('field_id');
                $table->integer('db_schema_id');
                $table->string('field_name')->default('');
            });
        }
    }

    private function ensureTenantAuthTablesExist(): void
    {
        if (!Schema::connection('tenant')->hasTable('user_info')) {
            Schema::connection('tenant')->create('user_info', static function (Blueprint $table): void {
                $table->integer('user_id')->primary();
                $table->string('login_id')->unique();
                $table->string('user_name');
                $table->integer('administrator_flag')->default(0);
                $table->integer('delete_flag')->default(0);
            });
        }

        if (!Schema::connection('tenant')->hasTable('password_info')) {
            Schema::connection('tenant')->create('password_info', static function (Blueprint $table): void {
                $table->integer('user_id');
                $table->string('password');
                $table->integer('password_type');
                $table->dateTime('regist_date');

                $table->index(['user_id']);
            });
        }

        if (!Schema::connection('tenant')->hasTable('account_lock')) {
            Schema::connection('tenant')->create('account_lock', static function (Blueprint $table): void {
                $table->integer('user_id')->primary();
                $table->integer('failure_count')->default(0);
                $table->dateTime('failure_date')->nullable();
                $table->integer('lock_flag')->default(0);
            });
        }

        if (!Schema::connection('tenant')->hasTable('personal_access_tokens')) {
            Schema::connection('tenant')->create('personal_access_tokens', static function (Blueprint $table): void {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function seedTenantAuthTestAccount(): void
    {
        $userId = 1001;
        $loginId = 'test-user';
        $plainPassword = 'p@ssw0rd';

        DB::connection('tenant')->table('user_info')->updateOrInsert(
            ['user_id' => $userId],
            [
                'login_id' => $loginId,
                'user_name' => 'Test User',
                'administrator_flag' => 0,
                'delete_flag' => 0,
            ],
        );

        DB::connection('tenant')->table('password_info')->updateOrInsert(
            ['user_id' => $userId],
            [
                'password' => PasswordHasher::hashLoginPassword($plainPassword, $userId),
                'password_type' => 1,
                'regist_date' => now()->toDateTimeString(),
            ],
        );

        DB::connection('tenant')->table('account_lock')->updateOrInsert(
            ['user_id' => $userId],
            [
                'failure_count' => 0,
                'failure_date' => null,
                'lock_flag' => 0,
            ],
        );
    }
}

