<?php

return [
    'open_router' => [
        'base_url' => 'https://openrouter.ai/api/v1',
        'api_key' => env('OPEN_ROUTER_API_KEY'),
        'model' => env('OPEN_ROUTER_MODEL', 'anthropic/claude-4.5-sonnet-20250929'),
    ],
];
