<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Send a chat completion request with streaming
     *
     * @param string $model The model ID to use
     * @param array $messages The conversation messages
     * @param array $options Additional options (temperature, max_tokens, tools, etc.)
     * @return \Generator Yields chunks of the response
     */
    public function chat(string $model, array $messages, array $options = []): \Generator;

    /**
     * Get the provider name
     */
    public function getName(): string;

    /**
     * Check if the provider supports a specific model
     */
    public function supportsModel(string $model): bool;
}
