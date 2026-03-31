<?php

namespace Database\Seeders;

use App\Modules\Auth\PasswordHasher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userId = 1001;
        $loginId = 'test-user';
        $plainPassword = 'p@ssw0rd';

        DB::table('user_info')->updateOrInsert(
            ['user_id' => $userId],
            [
                'login_id' => $loginId,
                'user_name' => 'Test User',
                'administrator_flag' => 0,
                'delete_flag' => 0,
            ],
        );

        DB::table('password_info')->updateOrInsert(
            ['user_id' => $userId],
            [
                'password' => PasswordHasher::hashLoginPassword($plainPassword, $userId),
                'password_type' => 1,
                'regist_date' => now()->toDateTimeString(),
            ],
        );

        DB::table('account_lock')->updateOrInsert(
            ['user_id' => $userId],
            [
                'failure_count' => 0,
                'failure_date' => null,
                'lock_flag' => 0,
            ],
        );
    }
}
