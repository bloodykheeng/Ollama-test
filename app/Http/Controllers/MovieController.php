<?php
namespace App\Http\Controllers;

use App\HasVectorEmbeddings;
use App\Models\Movie;
use Pgvector\Laravel\Distance;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class MovieController extends Controller
{
    use HasVectorEmbeddings;

    public function recommendManual(string $title)
    {
        $inputVector = $this->generateNomicEmbedTextEmbedding($title);

        $recommendations = Movie::select('id', 'title', 'description')
            ->orderByRaw("embedding <#> ?", [json_encode($inputVector)]) // cosine distance
            ->limit(5)
            ->get();

        return response()->json($recommendations);
    }

    public function recommend(string $title)
    {
        $inputVector = $this->generateNomicEmbedTextEmbedding($title);

        // Correct usage: pass vector array + distance type constant
        $recommendations = Movie::query()
            ->nearestNeighbors('embedding', $inputVector, Distance::Cosine)
            ->select('id', 'title', 'description')
            ->limit(5)
            ->get();

        // Generate AI summary for the best recommendation
        $aiSummary = null;
        if ($recommendations->isNotEmpty()) {
            $aiSummary = $this->generateAISummary($title, $recommendations);
        }

        return response()->json([
            'query'           => $title,
            'recommendations' => $recommendations,
            'ai_summary'      => $aiSummary,
        ]);
    }

    /**
     * Generate AI summary using Prism
     */
    private function generateAISummary(string $userQuery, $recommendations)
    {
        $response = "";

        try {
            // Prepare the movie list for the AI
            $movieList = $recommendations->map(function ($movie, $index) {
                return ($index + 1) . ". {$movie->title} - {$movie->description}";
            })->join("\n");

            $prompt = "Based on the user's search for '{$userQuery}', here are the top 5 recommended movies:\n\n{$movieList}\n\nAnalyze these recommendations and:\n1. Pick the ONE movie that best matches the user's interest in '{$userQuery}'\n2. Explain why this movie is the best choice\n3. Provide a compelling summary that would make the user want to watch it\n4. Keep your response engaging and under 150 words\n\nPlease format your response as: **Best Pick: [Movie Title]**\n[Your analysis and recommendation]";

            $response = Prism::text()
                ->using(Provider::Ollama, 'tinyllama') // Using Ollama with llama3.2
                ->withSystemPrompt('You are an expert movie critic and recommendation specialist who helps users find their perfect film based on their preferences.')
                ->withPrompt($prompt)
                ->withClientOptions(['timeout' => 120])
                ->asText();

            return $response->text;

        } catch (\Exception $e) {
            return "Error generating AI summary: " . $e->getMessage() . "response" . $response;
        }
    }

}
