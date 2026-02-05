<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;

class AnthropicProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $models;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://api.anthropic.com/v1';
        $this->models = $config['models'] ?? [];
    }

    public function getName(): string
    {
        return 'anthropic';
    }

    public function supportsModel(string $model): bool
    {
        return isset($this->models[$model]);
    }

    public function chat(string $model, array $messages, array $options = []): \Generator
    {
        $systemMessage = null;
        $conversationMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemMessage = is_string($message['content'])
                    ? $message['content']
                    : $this->extractTextFromContent($message['content']);
            } else {
                $conversationMessages[] = $this->formatMessage($message);
            }
        }

        $payload = [
            'model' => $model,
            'messages' => $conversationMessages,
            'max_tokens' => $options['max_tokens'] ?? 8192,
            'stream' => true,
        ];

        if ($systemMessage) {
            $payload['system'] = $systemMessage;
        }

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        if (isset($options['tools']) && !empty($options['tools'])) {
            $payload['tools'] = $this->formatTools($options['tools']);
        }

        $ch = curl_init("{$this->baseUrl}/messages");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
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
            $json = json_decode($data, true);

            if (!$json) {
                continue;
            }

            $type = $json['type'] ?? '';

            if ($type === 'content_block_delta') {
                $delta = $json['delta'] ?? [];

                if (isset($delta['text'])) {
                    yield [
                        'type' => 'text',
                        'content' => $delta['text'],
                    ];
                }

                if (isset($delta['partial_json'])) {
                    yield [
                        'type' => 'tool_input',
                        'content' => $delta['partial_json'],
                    ];
                }
            } elseif ($type === 'content_block_start') {
                $block = $json['content_block'] ?? [];

                if ($block['type'] === 'tool_use') {
                    yield [
                        'type' => 'tool_call_start',
                        'tool_call' => [
                            'id' => $block['id'],
                            'name' => $block['name'],
                        ],
                    ];
                }
            } elseif ($type === 'message_stop') {
                yield [
                    'type' => 'finish',
                    'reason' => 'stop',
                ];
            }
        }
    }

    protected function formatMessage(array $message): array
    {
        $formatted = ['role' => $message['role']];

        if (is_string($message['content'])) {
            $formatted['content'] = $message['content'];
        } elseif (is_array($message['content'])) {
            $formatted['content'] = array_map(function ($part) {
                if ($part['type'] === 'text') {
                    return ['type' => 'text', 'text' => $part['text']];
                } elseif ($part['type'] === 'image') {
                    return [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => $part['url'],
                        ],
                    ];
                }
                return $part;
            }, $message['content']);
        }

        return $formatted;
    }

    protected function formatTools(array $tools): array
    {
        return array_map(function ($tool) {
            return [
                'name' => $tool['function']['name'],
                'description' => $tool['function']['description'] ?? '',
                'input_schema' => $tool['function']['parameters'] ?? ['type' => 'object'],
            ];
        }, $tools);
    }

    protected function extractTextFromContent($content): string
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
