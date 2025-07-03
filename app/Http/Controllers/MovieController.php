<?php
namespace App\Http\Controllers;

use App\HasVectorEmbeddings;
use App\Models\Movie;
use Pgvector\Laravel\Distance;

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
            ->nearestNeighbors('embedding', $inputVector, Distance::L2)
            ->select('id', 'title', 'description')
            ->limit(5)
            ->get();

        return response()->json($recommendations);
    }
}
