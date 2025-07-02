<?php
namespace App\Console\Commands;

use App\Http\Controllers\MovieController;
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
    protected $signature = 'app:recommend-movie';

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
        $title = $this->argument('title') ?? text('Enter the movie title for recommendations');

        info("Generating recommendations for: \"$title\"");

        $recommendations = spin(
            fn() => (new MovieController())->recommend($title)->getData(),
            'Fetching similar movies...'
        );

        if (empty($recommendations)) {
            info("No recommendations found.");
            return Command::SUCCESS;
        }

        info("Top recommendations:");
        foreach ($recommendations as $movie) {
            $this->line("- {$movie->title}");
        }

        outro("Done. Enjoy your movie night!");
        return Command::SUCCESS;
    }
}