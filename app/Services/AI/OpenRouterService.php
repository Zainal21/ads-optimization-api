<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    /**
     * sendMessage
     *
     * @param  mixed  $prompt
     * @param  mixed  $model
     * @param  mixed  $maxTokens
     */
    public function sendMessage(string $prompt, int $maxTokens = 2000): string
    {
        $token = config('ai.open_router.api_key');

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('OPEN_ROUTER_API_KEY is not configured');
        }

        $response = $this->request($token, $prompt, $maxTokens);

        if ($response->failed()) {
            throw new \RuntimeException('AI Analysis failed: '.$response->body());
        }

        $content = $response->json();

        if (! isset($content['content'][0]['text'])) {
            throw new \RuntimeException('AI Analysis failed: unexpected response shape');
        }

        $text = $content['content'][0]['text'];

        if (! is_string($text) || $text === '') {
            throw new \RuntimeException('AI Analysis failed: unexpected response shape');
        }

        return $text;
    }

    /**
     * request
     *
     * @param  mixed  $apiKey
     * @param  mixed  $prompt
     * @param  mixed  $model
     * @param  mixed  $maxTokens
     */
    private function request(string $token, string $prompt, int $maxTokens): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
        ])->post(config('ai.open_router.base_url').'/messages', [
            'model' => config('ai.open_router.model', 'anthropic/claude-4.5-sonnet-20250929'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 1024,
            'temperature' => 0.7,
        ]);
    }
}
