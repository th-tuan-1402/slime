<?php

declare(strict_types=1);

namespace Tests\Feature\Record;

use App\Modules\Auth\PasswordHasher;
use App\Shared\TenantMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class RecordCsvApiTest extends TestCase
{
    private const USER_ID = 1001;
    private const LOGIN_ID = 'test-user';
    private const USER_NAME = 'Test User';
    private const PASSWORD_PLAIN = 'p@ssw0rd';

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
        $this->seedUserAndPassword();
        $this->setUpTenantTables();
    }

    private function setUpAuthTables(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('account_lock');
        Schema::dropIfExists('password_info');
        Schema::dropIfExists('user_info');

        Schema::create('user_info', static function (Blueprint $table): void {
            $table->integer('user_id')->primary();
            $table->string('login_id');
            $table->string('user_name');
            $table->integer('administrator_flag')->default(0);
            $table->integer('delete_flag')->default(0);
        });

        Schema::create('password_info', static function (Blueprint $table): void {
            $table->integer('user_id');
            $table->string('password');
            $table->integer('password_type');
            $table->dateTime('regist_date');
        });

        Schema::create('account_lock', static function (Blueprint $table): void {
            $table->integer('user_id')->primary();
            $table->integer('failure_count')->default(0);
            $table->dateTime('failure_date')->nullable();
            $table->integer('lock_flag')->default(0);
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

    private function seedUserAndPassword(): void
    {
        DB::table('user_info')->insert([
            'user_id' => self::USER_ID,
            'login_id' => self::LOGIN_ID,
            'user_name' => self::USER_NAME,
            'administrator_flag' => 1,
            'delete_flag' => 0,
        ]);

        DB::table('password_info')->insert([
            'user_id' => self::USER_ID,
            'password' => PasswordHasher::hashLoginPassword(self::PASSWORD_PLAIN, self::USER_ID),
            'password_type' => 1,
            'regist_date' => now()->toDateTimeString(),
        ]);
    }

    private function setUpTenantTables(): void
    {
        Schema::connection('tenant')->create('record_10', static function (Blueprint $table): void {
            $table->bigIncrements('record_id');
            $table->integer('record_outer_id')->nullable();
            $table->text('data_0_2001')->nullable();
        });

        DB::connection('tenant')->table('record_10')->insert([
            'record_outer_id' => 1,
            'data_0_2001' => 'Alpha',
        ]);
    }

    private function loginToken(): string
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ])->assertStatus(200);

        $token = (string) $login->json('data.token');
        $this->assertNotSame('', $token);

        return $token;
    }

    public function testExportCsv_WritesHeaderAndRows(): void
    {
        $token = $this->loginToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/v1/schemas/10/records/export');

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $lines = array_values(array_filter(preg_split("/\\r\\n|\\n|\\r/", trim($content)) ?: []));
        $this->assertGreaterThanOrEqual(2, count($lines));

        $header = str_getcsv($lines[0] ?? '');
        $this->assertContains('record_id', $header);
        $this->assertContains('record_outer_id', $header);
        $this->assertContains('data_0_2001', $header);

        $firstRow = array_combine($header, str_getcsv($lines[1] ?? '')) ?: [];
        $this->assertSame('1', (string) ($firstRow['record_outer_id'] ?? ''));
        $this->assertSame('Alpha', (string) ($firstRow['data_0_2001'] ?? ''));
    }

    public function testImportCsv_InsertsRowsAndReturnsRealInsertedCount(): void
    {
        $token = $this->loginToken();

        $csv = implode("\n", [
            'record_outer_id,data_0_2001',
            '99,Hello',
        ]);

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/v1/schemas/10/records/import', ['file' => $file]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.inserted', 1);

        $count = (int) DB::connection('tenant')
            ->table('record_10')
            ->where('record_outer_id', '=', 99)
            ->count();

        $this->assertSame(1, $count);
    }
}

