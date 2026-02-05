<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'chat_id',
        'message_id',
        'user_id',
        'is_upvoted',
    ];

    protected function casts(): array
    {
        return [
            'is_upvoted' => 'boolean',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
