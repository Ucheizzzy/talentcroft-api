<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $query->user_id = auth()->id();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function sentiments()
    {
        return $this->hasMany(PostSentiment::class);
    }

    public function getSentimentAttribute()
    {
        $sentiment = $this->sentiments()->where('user_id', auth()->id())->first();
        return  $sentiment ? $sentiment->sentiment : '';
    }

    public function getLikesAttribute()
    {
        return $this->sentiments()->likes()->count();
    }

    public function getDislikesAttribute()
    {
        return $this->sentiments()->dislikes()->count();
    }

       public static function getpost(){
        //raw sql
        $result = DB::select("SELECT * FROM posts");
        return $result;
    }
}
