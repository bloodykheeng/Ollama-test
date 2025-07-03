<?php
namespace App\Console\Commands;

use App\Http\Controllers\MovieController;
use App\Models\Movie;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use Illuminate\Console\Command;

class RecommendMovie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RecommendMovie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get movie recommendations based on a title';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show existing movies (limit 10)
        $this->line("🎬 Movies currently in the database:");
        $movies = Movie::select('title', 'description')->limit(10)->get();

        if ($movies->isEmpty()) {
            $this->warn("No movies found. Please seed the database first.");
        } else {
            foreach ($movies as $index => $movie) {
                $this->line("" . ($index + 1) . ". 🎭 {$movie->title}");
                $this->line("   📝 " . substr($movie->description, 0, 80) . "...");
                $this->line("");
            }
        }

        // Prompt for recommendation input
        $title = text('Enter a movie title or description for recommendations');

        info("🎬 Generating recommendations for: \"$title\"");

        $result = spin(
            fn() => (new MovieController())->recommend($title)->getData(),
            'Fetching similar movies and generating AI analysis...'
        );

        if (empty($result->recommendations)) {
            info("No recommendations found.");
            return Command::SUCCESS;
        }

        // Display recommendations
        info("🎯 Top recommendations:");
        foreach ($result->recommendations as $index => $movie) {
            $this->line("" . ($index + 1) . ". 🎭 {$movie->title}");
            $this->line("   📝 " . substr($movie->description, 0, 100) . "...");
            $this->line("");
        }

        // Display AI Summary
        if ($result->ai_summary) {
            $this->line("🤖 AI Movie Expert Analysis:");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->line($result->ai_summary);
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        }

        // Ask if user wants another recommendation
        if (confirm('Would you like to search for more movies?')) {
            return $this->handle();
        }

        outro("🍿 Done. Enjoy your movie night!");
        return Command::SUCCESS;
    }
}
