<?php

return [

    'login' => [
        'decay_minutes' => (int) env('LOGIN_RATE_LIMIT_DECAY_MINUTES', 1),
        'max_attempts_per_identifier' => (int) env('LOGIN_RATE_LIMIT_PER_IDENTIFIER', 5),
        'max_attempts_per_ip' => (int) env('LOGIN_RATE_LIMIT_PER_IP', 20),
        'min_failure_response_ms' => (int) env('LOGIN_MIN_FAILURE_RESPONSE_MS', 400),
    ],

    'password_reset' => [
        'decay_minutes' => (int) env('PASSWORD_RESET_RATE_LIMIT_DECAY_MINUTES', 10),
        'max_attempts_per_email' => (int) env('PASSWORD_RESET_RATE_LIMIT_PER_EMAIL', 3),
        'max_attempts_per_ip' => (int) env('PASSWORD_RESET_RATE_LIMIT_PER_IP', 10),
        'min_response_ms' => (int) env('PASSWORD_RESET_MIN_RESPONSE_MS', 400),
    ],

    'lockout' => [
        'max_failed_attempts' => (int) env('LOGIN_MAX_FAILED_ATTEMPTS', 3),
    ],

];
