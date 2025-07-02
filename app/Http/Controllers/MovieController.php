<?php
namespace App\Http\Controllers;

use App\HasVectorEmbeddings;
use App\Models\Movie;

class MovieController extends Controller
{
    use HasVectorEmbeddings;

    public function recommend(string $title)
    {
        $inputVector = $this->generateNomicEmbedTextEmbedding($title);

        $recommendations = Movie::select('id', 'title', 'description')
            ->orderByRaw("embedding <#> ?", [json_encode($inputVector)]) // cosine distance
            ->limit(5)
            ->get();

        return response()->json($recommendations);
    }
}