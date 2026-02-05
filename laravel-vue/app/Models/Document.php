<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'message_id',
        'title',
        'content',
        'kind',
        'language',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function isCode(): bool
    {
        return $this->kind === 'code';
    }

    public function isText(): bool
    {
        return $this->kind === 'text';
    }

    public function isSheet(): bool
    {
        return $this->kind === 'sheet';
    }
}
