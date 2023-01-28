<?php

namespace App\Models;

use App\Traits\UseNotifier;
use App\Traits\UseStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UseNotifier, UseStorage;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;

    protected $guarded = [];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token', 'otp', 'deleted_at', 'fcm_token',
        'blocked_at', 'blockage_reason', 'role_id', 'provider_name', 'provider_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = ['email_verified_at' => 'datetime', 'tags' => 'array'];

    /**
     * @return HasMany
     */
    public function bank()
    {
        return $this->hasMany(AccountDetails::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // public function movies()
    // {
    //     return $this->hasMany(Movie::class);
    // }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function lists()
    {
        return $this->hasMany(MovieList::class);
    }

    public function following()
    {
        return $this->hasMany(Following::class);
    }

    public function followers()
    {
        return $this->hasMany(Followers::class);
    }

    public function settings()
    {
        return $this->hasOne(Settings::class);
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn() => ucfirst($this->first_name) . " " . ucfirst($this->last_name),
        );
    }

    public function metrics(): Attribute
    {
        return Attribute::make(
            get: fn() => [
            'followers' => $this->followers->count(),
            'following' => $this->following->count(),
            'wallet' => nf(0, 2),
            // 'posts' => $this->post->count(),
        ],
        );
    }
}
