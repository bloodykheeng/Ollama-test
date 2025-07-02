<?php
namespace Database\Seeders;

use App\HasVectorEmbeddings;
use App\Models\Movie;
use Illuminate\Database\Seeder;

// php artisan db:seed --class=MovieSeeder

class MovieSeeder extends Seeder
{

    use HasVectorEmbeddings;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Movie::factory()->count(10)->create()->each(function ($movie) {
            $embedding = $this->generateNomicEmbedTextEmbedding($movie->title);
            $movie->update(['embedding' => $embedding]);
        });
    }
}