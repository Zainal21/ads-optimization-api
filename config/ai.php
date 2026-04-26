<?php

return [
    'anthropic' => [
        'base_url' => 'https://api.anthropic.com/v1',
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-2'),
    ],
    'grok' => [
        'base_url' => 'https://api.x.ai/v1',
        'api_token' => env('GROK_API_TOKEN'),
        'model' => env('GROK_MODEL', 'grok-4.20-reasoning'),
    ],
    'gemini' => [
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3-flash-preview'),
    ],
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),
];
