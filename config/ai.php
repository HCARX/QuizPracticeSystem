<?php

declare(strict_types=1);

return [
    'default_provider' => 'openai',

    'providers' => [
        'openai' => [
            'base_url' => getenv('AI_BASE_URL') ?: 'https://api.openai.com/v1',
            'api_key' => getenv('AI_API_KEY') ?: '',
            'default_model' => 'gpt-4o',
            'timeout' => 30,
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'retry' => 2,
        ],
    ],
];
