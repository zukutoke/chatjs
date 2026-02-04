<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'chat_id',
        'parent_message_id',
        'role',
        'attachments',
        'selected_model',
        'selected_tool',
        'active_stream_id',
        'canceled_at',
        'last_context',
        'annotations',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'last_context' => 'array',
            'annotations' => 'array',
            'canceled_at' => 'datetime',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function childMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(MessagePart::class)->orderBy('order', 'asc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeUser($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistant($query)
    {
        return $query->where('role', 'assistant');
    }

    public function getTextContentAttribute(): string
    {
        return $this->parts()
            ->where('type', 'text')
            ->pluck('text_content')
            ->implode("\n");
    }
}
