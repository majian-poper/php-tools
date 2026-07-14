<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Notifications\Notifiable;
use PHPTools\Approval\Contracts\Approver;
use PHPTools\LaravelCsvParser\Contracts\HasUniqueKey;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends BaseUser implements Approver, HasUniqueKey
{
    use HasFactory;
    use Notifiable;

    public static function newModel(): User
    {
        return User::factory()->create();
    }

    public static function newModels(int $count): array
    {
        return User::factory()->count($count)->create()->all();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Approver ---

    public function getApproverTitleAttribute(): string
    {
        return $this->name;
    }

    public function contains(Authenticatable $user): bool
    {
        return $this->is($user);
    }

    public function canRollBack(): bool
    {
        return $this->getKey() === 1;
    }

    // --- HasUniqueKey ---

    public function getUniqueKeyName(): string
    {
        return 'email';
    }

    public function getUniqueKey(): string
    {
        return $this->email;
    }

    public function getForeignModelKeys(): array
    {
        return [];
    }
}
