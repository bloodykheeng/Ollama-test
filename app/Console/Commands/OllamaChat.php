<?php
namespace App\Console\Commands;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class OllamaChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OllamaChat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chatting with ollama command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = text("ask ollama");
        info("Your prompt is : $prompt");
        $response = spin(fn() => $this->askOllama($prompt), 'Sending request...');
        info($response);

        // Get embeddings for same prompt
        $embeddingData = spin(fn() => $this->getNomicEmbedTextModelEmbeddings($prompt), 'Generating embeddings...');
        info('Embedding vector: ' . json_encode($embeddingData['embedding']));
        info("Tokens used: {$embeddingData['tokens']}");

        while ($prompt = text("ask ollama")) {
            $response = spin(fn() => $this->askOllama($prompt), 'Sending request...');
            info($response);
        }

        outro("Thanks, bye.");
    }

    /**
     * Chat with Ollama and return the response.
     */
    protected function askOllama(string $prompt): string
    {
        $response = Prism::text()
            ->using(Provider::Ollama, 'tinyllama')
            ->withPrompt($prompt)
            ->withClientOptions(['timeout' => 120])
            ->asText();

        return $response->text;
    }

    /**
     * Get text embeddings using OpenAI.
     */
    protected function getEmbeddings(string $input): array
    {
        $response = Prism::embeddings()
            ->using(Provider::OpenAI, 'text-embedding-3-large')
            ->fromInput($input)
            ->asEmbeddings();

        return [
            'embedding' => $response->embeddings[0]->embedding,
            'tokens'    => $response->usage->tokens,
        ];
    }

    /**
     * Get text embeddings using Ollama's /api/embeddings endpoint via Laravel HTTP client.
     */
    protected function getNomicEmbedTextModelEmbeddings(string $input): array
    {
        $response = Http::timeout(60)
            ->post('http://localhost:11434/api/embeddings', [
                'model'  => 'nomic-embed-text',
                'prompt' => $input,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to fetch embeddings: ' . $response->body());
        }

        $data = $response->json();

        return [
            'embedding' => $data['embedding'] ?? [],
            'tokens'    => strlen($input), // Ollama doesn't return token count, so we estimate or skip
        ];
    }
}