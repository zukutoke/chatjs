<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;

class XAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $models;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://api.x.ai/v1';
        $this->models = $config['models'] ?? [];
    }

    public function getName(): string
    {
        return 'xai';
    }

    public function supportsModel(string $model): bool
    {
        return isset($this->models[$model]);
    }

    public function chat(string $model, array $messages, array $options = []): \Generator
    {
        // xAI uses OpenAI-compatible API
        $payload = [
            'model' => $model,
            'messages' => $this->formatMessages($messages),
            'stream' => true,
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        if (isset($options['tools']) && !empty($options['tools'])) {
            $payload['tools'] = $options['tools'];
        }

        $ch = curl_init("{$this->baseUrl}/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$buffer) {
                $buffer .= $data;
                return strlen($data);
            },
        ]);

        $buffer = '';
        curl_exec($ch);
        curl_close($ch);

        $lines = explode("\n", $buffer);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || !str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = substr($line, 6);

            if ($data === '[DONE]') {
                break;
            }

            $json = json_decode($data, true);

            if (!$json || !isset($json['choices'][0])) {
                continue;
            }

            $choice = $json['choices'][0];
            $delta = $choice['delta'] ?? [];

            if (isset($delta['content'])) {
                yield [
                    'type' => 'text',
                    'content' => $delta['content'],
                ];
            }

            if (isset($choice['finish_reason']) && $choice['finish_reason']) {
                yield [
                    'type' => 'finish',
                    'reason' => $choice['finish_reason'],
                ];
            }
        }
    }

    protected function formatMessages(array $messages): array
    {
        return array_map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => is_string($message['content'])
                    ? $message['content']
                    : $this->extractText($message['content']),
            ];
        }, $messages);
    }

    protected function extractText($content): string
    {
        if (is_string($content)) {
            return $content;
        }

        $texts = [];
        foreach ($content as $part) {
            if (isset($part['text'])) {
                $texts[] = $part['text'];
            }
        }

        return implode("\n", $texts);
    }
}
