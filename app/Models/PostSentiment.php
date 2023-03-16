<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSentiment extends Model
{
    use HasFactory;

    protected $table = 'post__sentiments';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->user_id = auth()->id();
        });
    }

    public function scopeLikes($query)
    {
        return $query->where('sentiment', 'liked');
    }

    public function scopeDislikes($query)
    {
        return $query->where('sentiment', 'disliked');
    }


    public function getIsLikedAttribute()
    {
        return $this->sentiment === 'liked';
    }

    /**
     */
    public function getIsDislikedAttribute()
    {
        return $this->sentiment === 'disliked';
    }
}
