<?php

namespace App\Services\AI;

use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\DeepSeekProvider;
use App\Services\AI\Providers\GoogleProvider;
use App\Services\AI\Providers\MistralProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\XAIProvider;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected array $providers = [];

    public function __construct()
    {
        $this->initializeProviders();
    }

    protected function initializeProviders(): void
    {
        $config = config('ai.providers');

        if (!empty($config['openai']['api_key'])) {
            $this->providers['openai'] = new OpenAIProvider($config['openai']);
        }

        if (!empty($config['anthropic']['api_key'])) {
            $this->providers['anthropic'] = new AnthropicProvider($config['anthropic']);
        }

        if (!empty($config['google']['api_key'])) {
            $this->providers['google'] = new GoogleProvider($config['google']);
        }

        if (!empty($config['xai']['api_key'])) {
            $this->providers['xai'] = new XAIProvider($config['xai']);
        }

        if (!empty($config['mistral']['api_key'])) {
            $this->providers['mistral'] = new MistralProvider($config['mistral']);
        }

        if (!empty($config['deepseek']['api_key'])) {
            $this->providers['deepseek'] = new DeepSeekProvider($config['deepseek']);
        }
    }

    public function getAvailableModels(): array
    {
        $models = [];

        foreach ($this->providers as $providerName => $provider) {
            $providerConfig = config("ai.providers.{$providerName}");

            foreach ($providerConfig['models'] as $modelId => $modelConfig) {
                $models[] = [
                    'id' => $modelId,
                    'provider' => $providerName,
                    'name' => $modelConfig['name'],
                    'context_window' => $modelConfig['context_window'],
                    'max_tokens' => $modelConfig['max_tokens'],
                    'supports_vision' => $modelConfig['supports_vision'] ?? false,
                    'supports_tools' => $modelConfig['supports_tools'] ?? false,
                    'reasoning' => $modelConfig['reasoning'] ?? false,
                ];
            }
        }

        return $models;
    }

    public function getProvider(string $name): ?AIProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    public function getProviderForModel(string $modelId): ?AIProviderInterface
    {
        foreach (config('ai.providers') as $providerName => $providerConfig) {
            if (isset($providerConfig['models'][$modelId])) {
                return $this->providers[$providerName] ?? null;
            }
        }

        return null;
    }

    public function getModelConfig(string $modelId): ?array
    {
        foreach (config('ai.providers') as $providerName => $providerConfig) {
            if (isset($providerConfig['models'][$modelId])) {
                return array_merge(
                    $providerConfig['models'][$modelId],
                    ['provider' => $providerName]
                );
            }
        }

        return null;
    }

    public function chat(string $modelId, array $messages, array $options = []): \Generator
    {
        $provider = $this->getProviderForModel($modelId);

        if (!$provider) {
            throw new \InvalidArgumentException("Model not found: {$modelId}");
        }

        return $provider->chat($modelId, $messages, $options);
    }
}
