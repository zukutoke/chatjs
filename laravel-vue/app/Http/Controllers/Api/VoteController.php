<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index(Request $request, string $chatId): JsonResponse
    {
        $chat = Chat::where('id', $chatId)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()?->id)
                    ->orWhere('visibility', 'public');
            })
            ->firstOrFail();

        $votes = Vote::where('chat_id', $chatId)
            ->where('user_id', $request->user()->id)
            ->get()
            ->keyBy('message_id');

        return response()->json($votes);
    }

    public function vote(Request $request, string $chatId, string $messageId): JsonResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($chatId);
        $message = $chat->messages()->findOrFail($messageId);

        $validated = $request->validate([
            'is_upvoted' => ['required', 'boolean'],
        ]);

        Vote::updateOrCreate(
            [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'user_id' => $request->user()->id,
            ],
            [
                'is_upvoted' => $validated['is_upvoted'],
            ]
        );

        return response()->json(['message' => 'Vote recorded']);
    }

    public function removeVote(Request $request, string $chatId, string $messageId): JsonResponse
    {
        Vote::where('chat_id', $chatId)
            ->where('message_id', $messageId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Vote removed']);
    }
}
