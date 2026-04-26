<?php

namespace App\Services\AI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * sendMessage
     *
     * @param  mixed $prompt
     * @param  mixed $model
     * @param  mixed $maxTokens
     * @return string
     */
    public function sendMessage(string $prompt, int $maxTokens = 2000)
    {
        $token = config('ai.gemini.api_key');
        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('GEMINI_API_KEY is not configured');
        }

        $response = $this->request($token, $prompt, $maxTokens);

        if ($response->failed()) {
            throw new \RuntimeException('AI Analysis failed: '.$response->body());
        }

        $candidates = $response->json('candidates');
        $text = null;

        if (is_array($candidates) && isset($candidates[0]['output']) && is_string($candidates[0]['output'])) {
            $text = $candidates[0]['output'];
        }

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
            'x-goog-api-key' => $token,
        ])->post(sprintf('%s/models/%s:generateContent', config('ai.gemini.base_url'), config('ai.gemini.model', 'gemini-3-flash-preview')), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]);
    }
}
