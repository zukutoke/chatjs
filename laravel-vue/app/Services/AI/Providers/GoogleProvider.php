<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;

class GoogleProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $models;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta';
        $this->models = $config['models'] ?? [];
    }

    public function getName(): string
    {
        return 'google';
    }

    public function supportsModel(string $model): bool
    {
        return isset($this->models[$model]);
    }

    public function chat(string $model, array $messages, array $options = []): \Generator
    {
        $systemInstruction = null;
        $contents = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemInstruction = is_string($message['content'])
                    ? $message['content']
                    : $this->extractText($message['content']);
            } else {
                $contents[] = $this->formatMessage($message);
            }
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 8192,
            ],
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemInstruction]]];
        }

        if (isset($options['temperature'])) {
            $payload['generationConfig']['temperature'] = $options['temperature'];
        }

        $url = "{$this->baseUrl}/models/{$model}:streamGenerateContent?key={$this->apiKey}&alt=sse";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
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

            if (!$json || !isset($json['candidates'][0])) {
                continue;
            }

            $candidate = $json['candidates'][0];
            $content = $candidate['content'] ?? [];

            foreach ($content['parts'] ?? [] as $part) {
                if (isset($part['text'])) {
                    yield [
                        'type' => 'text',
                        'content' => $part['text'],
                    ];
                }
            }

            if (isset($candidate['finishReason'])) {
                yield [
                    'type' => 'finish',
                    'reason' => strtolower($candidate['finishReason']),
                ];
            }
        }
    }

    protected function formatMessage(array $message): array
    {
        $role = $message['role'] === 'assistant' ? 'model' : 'user';

        $parts = [];

        if (is_string($message['content'])) {
            $parts[] = ['text' => $message['content']];
        } elseif (is_array($message['content'])) {
            foreach ($message['content'] as $part) {
                if ($part['type'] === 'text') {
                    $parts[] = ['text' => $part['text']];
                } elseif ($part['type'] === 'image') {
                    $parts[] = [
                        'inlineData' => [
                            'mimeType' => 'image/jpeg',
                            'data' => $this->getImageData($part['url']),
                        ],
                    ];
                }
            }
        }

        return [
            'role' => $role,
            'parts' => $parts,
        ];
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

    protected function getImageData(string $url): string
    {
        if (str_starts_with($url, 'data:')) {
            return explode(',', $url)[1] ?? '';
        }

        $content = file_get_contents($url);
        return base64_encode($content);
    }
}
