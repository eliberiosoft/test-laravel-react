<?php

namespace App\Queries;

use App\Enums\LoginResult;
use App\Enums\UserStatus;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;

class DashboardMetricsQuery
{
    /**
     * @return array<string, array<string, int>>
     */
    public function get(): array
    {
        return [
            'accounts' => $this->accounts(),
            'sessions' => $this->sessions(),
            'failed_attempts' => $this->failedAttempts(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function accounts(): array
    {
        $byStatus = User::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            ...collect(UserStatus::cases())
                ->mapWithKeys(fn (UserStatus $status): array => [$status->value => (int) ($byStatus[$status->value] ?? 0)])
                ->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function sessions(): array
    {
        return [
            'active' => UserSession::query()->active()->count(),
            'started_last_24_hours' => UserSession::query()->where('started_at', '>=', now()->subDay())->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function failedAttempts(): array
    {
        $totals = LoginAttempt::query()
            ->toBase()
            ->whereIn('result', LoginResult::failureValues())
            ->where('attempted_at', '>=', now()->subDays(7))
            ->selectRaw('count(*) as last_7_days')
            ->selectRaw('coalesce(sum(case when attempted_at >= ? then 1 else 0 end), 0) as last_24_hours', [now()->subDay()])
            ->selectRaw('coalesce(sum(case when user_id is null then 1 else 0 end), 0) as unknown_accounts_last_7_days')
            ->first();

        return [
            'last_24_hours' => (int) $totals->last_24_hours,
            'last_7_days' => (int) $totals->last_7_days,
            'unknown_accounts_last_7_days' => (int) $totals->unknown_accounts_last_7_days,
        ];
    }
}
