<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use SensitiveParameter;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['person_id', 'username', 'email', 'password'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected string $guard_name = PERMISSION_GUARD;

    protected $attributes = [
        'status' => UserStatus::Active->value,
        'failed_login_attempts' => 0,
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => UserStatus::class,
            'failed_login_attempts' => 'integer',
            'locked_at' => 'datetime',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn (string $value): string => self::normalizeEmail($value));
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function sendPasswordResetNotification(#[SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isBlocked(): bool
    {
        return $this->status === UserStatus::Blocked;
    }

    #[Scope]
    protected function whereLogin(Builder $query, string $login): void
    {
        $login = trim($login);

        if (str_contains($login, '@')) {
            $query->where('email', self::normalizeEmail($login));

            return;
        }

        $query->where('username', $login);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logOnly(['username', 'email', 'status', 'locked_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    private static function normalizeEmail(string $email): string
    {
        return Str::of($email)->trim()->lower()->toString();
    }
}
