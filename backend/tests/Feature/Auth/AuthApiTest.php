<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\PasswordHasher;
use App\Shared\TenantMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

final class AuthApiTest extends TestCase
{
    private const USER_ID = 1001;
    private const LOGIN_ID = 'test-user';
    private const USER_NAME = 'Test User';
    private const PASSWORD_PLAIN = 'p@ssw0rd';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TenantMiddleware::class);

        $this->setUpTables();

        $this->seedUserAndPassword();
    }

    private function setUpTables(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('account_lock');
        Schema::dropIfExists('password_info');
        Schema::dropIfExists('user_info');

        Schema::create('user_info', static function ($table): void {
            $table->integer('user_id')->primary();
            $table->string('login_id');
            $table->string('user_name');
            $table->integer('administrator_flag')->default(0);
            $table->integer('delete_flag')->default(0);
        });

        Schema::create('password_info', static function ($table): void {
            $table->integer('user_id');
            $table->string('password');
            $table->integer('password_type');
            $table->dateTime('regist_date');
        });

        Schema::create('account_lock', static function ($table): void {
            $table->integer('user_id')->primary();
            $table->integer('failure_count')->default(0);
            $table->dateTime('failure_date')->nullable();
            $table->integer('lock_flag')->default(0);
        });

        Schema::create('personal_access_tokens', static function ($table): void {
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
            'administrator_flag' => 0,
            'delete_flag' => 0,
        ]);

        DB::table('password_info')->insert([
            'user_id' => self::USER_ID,
            'password' => PasswordHasher::hashLoginPassword(self::PASSWORD_PLAIN, self::USER_ID),
            'password_type' => 1,
            'regist_date' => now()->toDateTimeString(),
        ]);
    }

    public function testLogin_Success_ReturnsToken(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.user.user_id', self::USER_ID);
        $response->assertJsonPath('data.user.login_id', self::LOGIN_ID);
        $response->assertJsonPath('data.user.user_name', self::USER_NAME);
        $response->assertJsonPath('data.token', $response->json('data.token'));
        $this->assertNotSame('', (string) $response->json('data.token'));
    }

    public function testSanctumPersonalAccessTokenModel_Count_DoesNotCrash(): void
    {
        $count = PersonalAccessToken::query()->count();
        $this->assertSame(0, $count);
    }

    public function testLogin_MissingFields_Returns422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
    }

    public function testLogin_InvalidPassword_Returns401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function testLogin_InvalidPassword_IncrementsFailureCount(): void
    {
        config()->set('auth_module.account_lock_enabled', true);
        config()->set('auth_module.login_failure_limit', 5);
        config()->set('auth_module.login_failure_reset_time_minutes', 30);

        $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => 'wrong',
        ])->assertStatus(401);

        $row = DB::table('account_lock')->where('user_id', '=', self::USER_ID)->first();
        $this->assertNotNull($row);
        $this->assertGreaterThanOrEqual(1, (int) $row->failure_count);
    }

    public function testLogin_ReachesLimit_ReturnsAccountLocked(): void
    {
        config()->set('auth_module.account_lock_enabled', true);
        config()->set('auth_module.login_failure_limit', 2);
        config()->set('auth_module.account_unlock_period_minutes', 30);
        config()->set('auth_module.login_failure_reset_time_minutes', 30);

        $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => 'wrong',
        ])->assertStatus(401);

        $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => 'wrong',
        ])->assertStatus(401);

        $response = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Account is locked.');
    }

    public function testMe_Unauthorized_Returns401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function testMe_Authorized_Returns200(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ])->assertStatus(200);

        $token = (string) $login->json('data.token');

        $me = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $me->assertStatus(200);
        $me->assertJsonPath('data.user.user_id', self::USER_ID);
    }

    public function testLogout_RevokesToken(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ])->assertStatus(200);

        $token = (string) $login->json('data.token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(204);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function testRefreshToken_ReturnsNewToken(): void
    {
        $login = $this->postJson('/api/v1/auth/login', [
            'login_id' => self::LOGIN_ID,
            'password' => self::PASSWORD_PLAIN,
        ])->assertStatus(200);

        $token = (string) $login->json('data.token');

        $refresh = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/token');

        $refresh->assertStatus(200);
        $this->assertNotSame('', (string) $refresh->json('data.token'));
    }
}

