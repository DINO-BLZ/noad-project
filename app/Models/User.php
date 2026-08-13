<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Drop;
use App\Models\DropWhitelist;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function dropWhitelists()
    {
        return $this->hasMany(DropWhitelist::class);
    }

    public function whitelistedDrops()
    {
        return $this->belongsToMany(Drop::class, 'drop_whitelists')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function approvedDropWhitelists()
    {
        return $this->hasMany(DropWhitelist::class)->where('status', 'approved');
    }

    public function isWhitelistedForDrop(Drop $drop): bool
    {
        return $this->approvedDropWhitelists()->where('drop_id', $drop->id)->exists();
    }
}
