<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Movie extends Model
{
    use HasNeighbors, HasFactory;

    protected $table = 'movies';

    protected $fillable = [
        'title',
        'description',
        'embedding',
    ];

    protected $casts = [
        'embedding' => Vector::class,
    ];
}
