<?php
namespace App;

use Illuminate\Support\Facades\Http;

trait HasVectorEmbeddings
{
    //
    public function generateNomicEmbedTextEmbedding(string $text): array
    {
        $response = Http::timeout(60)
            ->post('http://localhost:11434/api/embeddings', [
                'model'  => 'nomic-embed-text',
                'prompt' => $text,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Embedding failed: ' . $response->body());
        }

        return $response->json()['embedding'] ?? [];
    }
}