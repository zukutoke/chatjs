<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessagePart extends Model
{
    use HasUuids;

    protected $fillable = [
        'message_id',
        'type',
        'order',
        'text_content',
        'reasoning_content',
        'file_media_type',
        'file_filename',
        'file_url',
        'source_url',
        'source_title',
        'source_description',
        'tool_name',
        'tool_call_id',
        'tool_state',
        'tool_input',
        'tool_output',
        'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
            'tool_input' => 'array',
            'tool_output' => 'array',
            'provider_metadata' => 'array',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function isText(): bool
    {
        return $this->type === 'text';
    }

    public function isReasoning(): bool
    {
        return $this->type === 'reasoning';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isToolCall(): bool
    {
        return str_starts_with($this->type, 'tool-');
    }

    public function isSource(): bool
    {
        return str_starts_with($this->type, 'source-');
    }
}
