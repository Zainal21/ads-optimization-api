<?php

return [
    'open_router' => [
        'base_url' => 'https://openrouter.ai/api/v1',
        'api_key' => env('OPEN_ROUTER_API_KEY', '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'),
        'model' => env('OPEN_ROUTER_MODEL', 'anthropic/claude-4.5-sonnet-20250929'),
    ],
];
