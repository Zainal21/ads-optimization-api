<?php

return [
    'open_router' => [
        'base_url' => 'https://openrouter.ai/api/v1',
        'api_key' => env('OPEN_ROUTER_API_KEY', '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'),
        'model' => env('OPEN_ROUTER_MODEL', 'anthropic/claude-4.5-sonnet-20250929'),
    ],
];
