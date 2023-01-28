<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Following extends Model
{
    use HasFactory;

  
    protected $fillable = ['following_id', 'user_id'];

    protected $hidden = ['id','updated_at', 'deleted_at','created_at','user_id'];

    /**
     * @return void
     */

     public function user(): HasMany
    {
        return $this->HasMany(User::class);
    }
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->following_id = auth()->id();
        });
    }

    
}
