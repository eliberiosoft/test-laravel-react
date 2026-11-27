<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request): array => $this->loginLimits($request));
        RateLimiter::for('password-reset-link', fn (Request $request): array => $this->passwordResetLimits($request));
        RateLimiter::for('password-reset', fn (Request $request): array => $this->passwordResetLimits($request));
    }

    private function loginLimits(Request $request): array
    {
        $config = config('security.login');

        return [
            Limit::perMinutes($config['decay_minutes'], $config['max_attempts_per_identifier'])
                ->by('login:identifier:'.hash('sha256', $this->normalizedInput($request, LOGIN_FIELD))),
            Limit::perMinutes($config['decay_minutes'], $config['max_attempts_per_ip'])
                ->by('login:ip:'.hash('sha256', (string) $request->ip())),
        ];
    }

    private function passwordResetLimits(Request $request): array
    {
        $config = config('security.password_reset');

        return [
            Limit::perMinutes($config['decay_minutes'], $config['max_attempts_per_email'])
                ->by('password-reset:email:'.hash('sha256', $this->normalizedInput($request, 'email'))),
            Limit::perMinutes($config['decay_minutes'], $config['max_attempts_per_ip'])
                ->by('password-reset:ip:'.hash('sha256', (string) $request->ip())),
        ];
    }

    private function normalizedInput(Request $request, string $field): string
    {
        $value = $request->input($field);

        return is_string($value) ? Str::of($value)->trim()->lower()->toString() : '';
    }
}
