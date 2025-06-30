<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('chatone', function () {
    // $response = Prism::text()
    //     ->using(Provider::OpenAI, 'gpt-4.1')
    //     ->withPrompt("tell me a story about bloodykheeng")
    //     ->asText();

    $response = Prism::text()
        ->using(Provider::Ollama, 'tinyllama')
        ->withPrompt("whats a plant")
        ->withClientOptions(['timeout' => 120])
        ->asText();

    // $response = Prism::text()
    //     ->using(Provider::DeepSeek, 'DeepSeek-R1-0528')
    //     ->withPrompt("1+1")
    //     ->withClientOptions(['timeout' => 120])
    //     ->asText();

    // $response->text
    return response()->json([$response->usage->promptTokens, $response->text]);
});

Route::get('hello', function () {
    return response()->json(['helo world']);
});