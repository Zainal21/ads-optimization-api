<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AnthropicService
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
        $apiKey = config('ai.anthropic.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new \RuntimeException('ANTHROPIC_API_KEY is not configured');
        }

        $response = $this->request($apiKey, $prompt, $maxTokens);

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
    private function request(string $apiKey, string $prompt, int $maxTokens): Response
    {
        return Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->post(config('ai.anthropic.base_url').'/messages', [
            'model' => config('ai.anthropic.model', 'claude-2'),
            'max_tokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);
    }
}
