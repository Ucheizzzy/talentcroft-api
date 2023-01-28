<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    use HasFactory;

    protected $fillable = ['follower_id', 'user_id'];

    protected $hidden = ['id','updated_at', 'deleted_at','created_at','user_id'];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->follower_id = auth()->id();
        });
    }

    public function user(): HasMany
    {
        return $this->HasMany(User::class);
    }
}
