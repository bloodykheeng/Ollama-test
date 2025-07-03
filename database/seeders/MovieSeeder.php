<?php
namespace Database\Seeders;

use App\HasVectorEmbeddings;
use App\Models\Movie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

// php artisan db:seed --class=MovieSeeder

class MovieSeeder extends Seeder
{

    use HasVectorEmbeddings;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching movies from IMDb API...');

        $movies = $this->fetchMoviesFromImdb();

        if (empty($movies)) {
            $this->command->error('No valid movie data received from IMDb.');
            return;
        }

        $this->command->info('✓ Movies fetched successfully');

        // Shuffle the movies using Laravel Collection
        $shuffledMovies = collect($movies)->shuffle();

        $savedCount = 0;

        foreach ($shuffledMovies as $movie) {
            if ($savedCount >= 10) {
                break;
            }

            $title       = $movie['primaryTitle'] ?? null;
            $description = $movie['description'] ?? null;

            if (! $title || ! $description) {
                continue;
            }

            if (Movie::where('title', $title)->exists()) {
                $this->command->info("⏩ Movie '{$title}' already exists, skipping...");
                continue;
            }

            $this->command->info("Saving movie: {$title}");

            $combinedText = "{$title}. {$description}";
            $embedding    = $this->generateNomicEmbedTextEmbedding($combinedText);

            Movie::create([
                'title'       => $title,
                'description' => $description,
                'embedding'   => $embedding,
            ]);

            $this->command->info("✓ Movie '{$title}' saved successfully");
            $this->command->line('');

            $savedCount++;
        }

        $this->command->info("Finished seeding {$savedCount} new movies!");
    }

    /**
     * Fetch movies from IMDb API via RapidAPI
     */
    private function fetchMoviesFromImdb(): array
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => env('RAPIDAPI_HOST'),
            'x-rapidapi-key'  => env('RAPIDAPI_KEY'),
        ])->get('https://imdb236.p.rapidapi.com/api/imdb/top250-movies');

        if (! $response->successful()) {
            $this->command->error('Failed to fetch movies. Status: ' . $response->status());
            return [];
        }

        $movies = $response->json();

        return is_array($movies) ? $movies : [];
    }
}
