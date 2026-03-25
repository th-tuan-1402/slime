<?php

declare(strict_types=1);

return [
    'account_lock_enabled' => (bool) env('AUTH_ACCOUNT_LOCK_ENABLED', true),
    'login_failure_limit' => (int) env('AUTH_LOGIN_FAILURE_LIMIT', 5),
    'account_unlock_period_minutes' => (int) env('AUTH_ACCOUNT_UNLOCK_PERIOD_MINUTES', 30),
    'login_failure_reset_time_minutes' => (int) env('AUTH_LOGIN_FAILURE_RESET_TIME_MINUTES', 30),
];

