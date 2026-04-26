<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GrokService
{
    /**
     * sendMessage
     *
     * @param  mixed $prompt
     * @param  mixed $model
     * @param  mixed $maxTokens
     * @return string
     */
    public function sendMessage(string $prompt, int $maxTokens = 2000): string
    {
        $token = config('ai.grok.api_token');

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('GROK_API_TOKEN is not configured');
        }

        $response = $this->request($token, $prompt, $maxTokens);

        if ($response->failed()) {
            throw new \RuntimeException('AI Analysis failed: '.$response->body());
        }

        $content = $response->json('content');
        $text = is_array($content) ? ($content[0]['text'] ?? null) : null;

        if (! is_string($text) || $text === '') {
            throw new \RuntimeException('AI Analysis failed: unexpected response shape');
        }

        return $text;
    }

    /**
     * request
     *
     * @param  mixed $apiKey
     * @param  mixed $prompt
     * @param  mixed $model
     * @param  mixed $maxTokens
     * @return Response
     */
    private function request(string $token, string $prompt, int $maxTokens): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(config('ai.grok.base_url').'/responses', [
            'model' => config('ai.grok.model', 'grok-4.20-reasoning'),
            'input' => $prompt,
        ]);
    }
}
