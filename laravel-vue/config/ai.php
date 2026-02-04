<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "openai", "anthropic", "google", "xai", "mistral", "deepseek"
    |
    */
    'default' => env('DEFAULT_AI_PROVIDER', 'openai'),
    'default_model' => env('DEFAULT_AI_MODEL', 'gpt-4o'),

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => 'https://api.openai.com/v1',
            'models' => [
                'gpt-4o' => [
                    'name' => 'GPT-4o',
                    'context_window' => 128000,
                    'max_tokens' => 16384,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'gpt-4o-mini' => [
                    'name' => 'GPT-4o Mini',
                    'context_window' => 128000,
                    'max_tokens' => 16384,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'gpt-4-turbo' => [
                    'name' => 'GPT-4 Turbo',
                    'context_window' => 128000,
                    'max_tokens' => 4096,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'o1' => [
                    'name' => 'o1',
                    'context_window' => 200000,
                    'max_tokens' => 100000,
                    'supports_vision' => true,
                    'supports_tools' => false,
                    'reasoning' => true,
                ],
                'o1-mini' => [
                    'name' => 'o1-mini',
                    'context_window' => 128000,
                    'max_tokens' => 65536,
                    'supports_vision' => false,
                    'supports_tools' => false,
                    'reasoning' => true,
                ],
            ],
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => 'https://api.anthropic.com/v1',
            'models' => [
                'claude-3-5-sonnet-20241022' => [
                    'name' => 'Claude 3.5 Sonnet',
                    'context_window' => 200000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'claude-3-5-haiku-20241022' => [
                    'name' => 'Claude 3.5 Haiku',
                    'context_window' => 200000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'claude-3-opus-20240229' => [
                    'name' => 'Claude 3 Opus',
                    'context_window' => 200000,
                    'max_tokens' => 4096,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
            ],
        ],
        'google' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'models' => [
                'gemini-2.0-flash-exp' => [
                    'name' => 'Gemini 2.0 Flash',
                    'context_window' => 1000000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'gemini-1.5-pro' => [
                    'name' => 'Gemini 1.5 Pro',
                    'context_window' => 2000000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'gemini-1.5-flash' => [
                    'name' => 'Gemini 1.5 Flash',
                    'context_window' => 1000000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
            ],
        ],
        'xai' => [
            'api_key' => env('XAI_API_KEY'),
            'base_url' => 'https://api.x.ai/v1',
            'models' => [
                'grok-2' => [
                    'name' => 'Grok 2',
                    'context_window' => 128000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'grok-2-mini' => [
                    'name' => 'Grok 2 Mini',
                    'context_window' => 128000,
                    'max_tokens' => 8192,
                    'supports_vision' => false,
                    'supports_tools' => true,
                ],
            ],
        ],
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY'),
            'base_url' => 'https://api.mistral.ai/v1',
            'models' => [
                'mistral-large-latest' => [
                    'name' => 'Mistral Large',
                    'context_window' => 128000,
                    'max_tokens' => 8192,
                    'supports_vision' => true,
                    'supports_tools' => true,
                ],
                'mistral-small-latest' => [
                    'name' => 'Mistral Small',
                    'context_window' => 128000,
                    'max_tokens' => 8192,
                    'supports_vision' => false,
                    'supports_tools' => true,
                ],
                'codestral-latest' => [
                    'name' => 'Codestral',
                    'context_window' => 32000,
                    'max_tokens' => 8192,
                    'supports_vision' => false,
                    'supports_tools' => true,
                ],
            ],
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'base_url' => 'https://api.deepseek.com/v1',
            'models' => [
                'deepseek-chat' => [
                    'name' => 'DeepSeek Chat',
                    'context_window' => 64000,
                    'max_tokens' => 8192,
                    'supports_vision' => false,
                    'supports_tools' => true,
                ],
                'deepseek-reasoner' => [
                    'name' => 'DeepSeek Reasoner',
                    'context_window' => 64000,
                    'max_tokens' => 8192,
                    'supports_vision' => false,
                    'supports_tools' => false,
                    'reasoning' => true,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Credits Configuration
    |--------------------------------------------------------------------------
    */
    'credits' => [
        'default' => (int) env('DEFAULT_USER_CREDITS', 50),
        'cost_per_1k_tokens' => [
            'input' => 0.01,
            'output' => 0.03,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Tools Configuration
    |--------------------------------------------------------------------------
    */
    'tools' => [
        'web_search' => [
            'enabled' => env('TAVILY_API_KEY') !== null,
            'provider' => 'tavily',
            'api_key' => env('TAVILY_API_KEY'),
        ],
        'url_retrieval' => [
            'enabled' => env('FIRECRAWL_API_KEY') !== null,
            'provider' => 'firecrawl',
            'api_key' => env('FIRECRAWL_API_KEY'),
        ],
    ],
];
