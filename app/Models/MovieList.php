<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieList extends Model
{
    use HasFactory;

    protected $table = 'movie_lists';

    protected $primaryKey = 'id';

    protected $guarded = [];

    // protected $casts = ['content' => 'array'];

    public function files()
    {
         return $this->hasManyThrough(MovieFile::class, Movie::class);
    } 

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');   
    }
    
    public function movies()
    {
        return $this->hasMany(Movie::class);
    }
}
