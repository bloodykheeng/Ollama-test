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
        $this->command->info('Starting to seed movies...');

        for ($i = 0; $i < 10; $i++) {
            $this->command->info("Creating movie " . ($i + 1) . "/10");

            $title       = fake()->sentence(3);
            $description = fake()->paragraph();

            $this->command->info("Generating embedding for: {$title}");
            $combinedText = "{$title}. {$description}";
            $embedding    = $this->generateNomicEmbedTextEmbedding($combinedText);

            Movie::create([
                'title'       => $title,
                'description' => $description,
                'embedding'   => $embedding,
            ]);

            $this->command->info("✓ Movie created successfully");
            $this->command->line(''); // Empty line for better readability
        }

        $this->command->info('Finished seeding movies!');
    }
}
