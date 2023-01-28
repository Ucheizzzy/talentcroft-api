<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies';

    protected $fillable = ['name', 'description', 'type', 'tags', 'status', 'age_rating','director', 'year', 'genre'];
    protected $searchable = ['name', 'description'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function list()
    {
        return $this->belongsTo(MovieList::class);
    }

    public function file()
    {
        return $this->hasOne(MovieFile::class);
    }

    public function episodes()
    {
        return $this->hasMany(MovieFile::class);
    }

    protected $casts = ['tags' => 'array'];
}
