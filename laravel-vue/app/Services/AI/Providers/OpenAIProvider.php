<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $models;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://api.openai.com/v1';
        $this->models = $config['models'] ?? [];
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function supportsModel(string $model): bool
    {
        return isset($this->models[$model]);
    }

    public function chat(string $model, array $messages, array $options = []): \Generator
    {
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
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse SSE response
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

            if (isset($delta['tool_calls'])) {
                foreach ($delta['tool_calls'] as $toolCall) {
                    yield [
                        'type' => 'tool_call',
                        'tool_call' => $toolCall,
                    ];
                }
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
            $formatted = [
                'role' => $message['role'],
            ];

            if (is_string($message['content'])) {
                $formatted['content'] = $message['content'];
            } elseif (is_array($message['content'])) {
                // Multi-modal content
                $formatted['content'] = array_map(function ($part) {
                    if ($part['type'] === 'text') {
                        return ['type' => 'text', 'text' => $part['text']];
                    } elseif ($part['type'] === 'image') {
                        return [
                            'type' => 'image_url',
                            'image_url' => ['url' => $part['url']],
                        ];
                    }
                    return $part;
                }, $message['content']);
            }

            return $formatted;
        }, $messages);
    }
}
