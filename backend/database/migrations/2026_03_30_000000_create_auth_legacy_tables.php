<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_info', static function (Blueprint $table): void {
            $table->integer('user_id')->primary();
            $table->string('login_id')->unique();
            $table->string('user_name');
            $table->integer('administrator_flag')->default(0);
            $table->integer('delete_flag')->default(0);
        });

        Schema::create('password_info', static function (Blueprint $table): void {
            $table->integer('user_id');
            $table->string('password');
            $table->integer('password_type');
            $table->dateTime('regist_date');

            $table->index(['user_id']);
        });

        Schema::create('account_lock', static function (Blueprint $table): void {
            $table->integer('user_id')->primary();
            $table->integer('failure_count')->default(0);
            $table->dateTime('failure_date')->nullable();
            $table->integer('lock_flag')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_lock');
        Schema::dropIfExists('password_info');
        Schema::dropIfExists('user_info');
    }
};

