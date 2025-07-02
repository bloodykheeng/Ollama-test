<?php
namespace App\Console\Commands;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use Illuminate\Console\Command;
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
}