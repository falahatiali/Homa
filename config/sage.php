<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | package. You can change this to any of the supported providers below.
    |
    | Supported: "openai", "anthropic"
    |
    */

    'default' => env('SAGE_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for each AI provider. Each provider
    | requires different credentials and settings.
    |
    */

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1000),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default System Prompt
    |--------------------------------------------------------------------------
    |
    | This is the default system prompt that will be used for all AI
    | conversations unless overridden.
    |
    */

    'system_prompt' => env('SAGE_SYSTEM_PROMPT', 'You are a helpful AI assistant.'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of AI interactions for debugging purposes.
    |
    */

    'logging' => [
        'enabled' => env('SAGE_LOGGING', false),
        'channel' => env('SAGE_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching for AI responses to reduce API calls and costs.
    |
    */

    'cache' => [
        'enabled' => env('SAGE_CACHE_ENABLED', false),
        'ttl' => env('SAGE_CACHE_TTL', 3600), // in seconds
        'prefix' => 'sage_',
    ],

];

