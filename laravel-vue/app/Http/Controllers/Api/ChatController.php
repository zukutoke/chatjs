<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessagePart;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(
        protected AIService $aiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Chat::forUser($user->id)
            ->with(['project:id,name,icon,icon_color'])
            ->withCount('messages')
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at');

        if ($request->has('project_id')) {
            $query->inProject($request->project_id);
        }

        $chats = $query->paginate($request->get('per_page', 50));

        return response()->json($chats);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = Chat::create([
            'user_id' => $request->user()->id,
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'] ?? 'New Chat',
        ]);

        return response()->json($chat, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $chat = Chat::where('id', $id)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()?->id)
                    ->orWhere('visibility', 'public');
            })
            ->with(['project:id,name,icon,icon_color,instructions'])
            ->firstOrFail();

        return response()->json($chat);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'visibility' => ['sometimes', 'in:public,private'],
            'is_pinned' => ['sometimes', 'boolean'],
            'project_id' => ['sometimes', 'nullable', 'uuid', 'exists:projects,id'],
        ]);

        $chat->update($validated);

        return response()->json($chat);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($id);
        $chat->delete();

        return response()->json(['message' => 'Chat deleted']);
    }

    public function messages(Request $request, string $id): JsonResponse
    {
        $chat = Chat::where('id', $id)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()?->id)
                    ->orWhere('visibility', 'public');
            })
            ->firstOrFail();

        $messages = $chat->messages()
            ->with('parts')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return $this->formatMessage($message);
            });

        return response()->json($messages);
    }

    public function sendMessage(Request $request, string $id): StreamedResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'model' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
        ]);

        // Create user message
        $userMessage = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'selected_model' => $validated['model'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        MessagePart::create([
            'message_id' => $userMessage->id,
            'type' => 'text',
            'order' => 0,
            'text_content' => $validated['content'],
        ]);

        // Create assistant message placeholder
        $assistantMessage = Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'selected_model' => $validated['model'],
            'active_stream_id' => Str::uuid()->toString(),
        ]);

        return response()->stream(function () use ($chat, $assistantMessage, $validated) {
            // Get conversation history
            $messages = $this->buildConversationMessages($chat);

            // Add project instructions if available
            if ($chat->project?->instructions) {
                array_unshift($messages, [
                    'role' => 'system',
                    'content' => $chat->project->instructions,
                ]);
            }

            $fullContent = '';
            $reasoningContent = '';

            try {
                foreach ($this->aiService->chat($validated['model'], $messages) as $chunk) {
                    if ($chunk['type'] === 'text') {
                        $fullContent .= $chunk['content'];
                        echo "data: " . json_encode([
                            'type' => 'text',
                            'content' => $chunk['content'],
                        ]) . "\n\n";
                        ob_flush();
                        flush();
                    } elseif ($chunk['type'] === 'reasoning') {
                        $reasoningContent .= $chunk['content'];
                        echo "data: " . json_encode([
                            'type' => 'reasoning',
                            'content' => $chunk['content'],
                        ]) . "\n\n";
                        ob_flush();
                        flush();
                    } elseif ($chunk['type'] === 'finish') {
                        echo "data: " . json_encode([
                            'type' => 'finish',
                            'message_id' => $assistantMessage->id,
                        ]) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                // Save the message parts
                $order = 0;

                if ($reasoningContent) {
                    MessagePart::create([
                        'message_id' => $assistantMessage->id,
                        'type' => 'reasoning',
                        'order' => $order++,
                        'reasoning_content' => $reasoningContent,
                    ]);
                }

                if ($fullContent) {
                    MessagePart::create([
                        'message_id' => $assistantMessage->id,
                        'type' => 'text',
                        'order' => $order++,
                        'text_content' => $fullContent,
                    ]);
                }

                // Update assistant message
                $assistantMessage->update([
                    'active_stream_id' => null,
                ]);

                // Update chat title if it's the first message
                if ($chat->title === 'New Chat') {
                    $title = Str::limit($validated['content'], 50);
                    $chat->update(['title' => $title]);
                }

            } catch (\Exception $e) {
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function stopStream(Request $request, string $chatId, string $messageId): JsonResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($chatId);
        $message = $chat->messages()->findOrFail($messageId);

        $message->update([
            'canceled_at' => now(),
            'active_stream_id' => null,
        ]);

        return response()->json(['message' => 'Stream stopped']);
    }

    public function deleteTrailingMessages(Request $request, string $chatId, string $messageId): JsonResponse
    {
        $chat = Chat::forUser($request->user()->id)->findOrFail($chatId);
        $message = $chat->messages()->findOrFail($messageId);

        // Delete all messages after this one
        $chat->messages()
            ->where('created_at', '>', $message->created_at)
            ->delete();

        return response()->json(['message' => 'Trailing messages deleted']);
    }

    protected function buildConversationMessages(Chat $chat): array
    {
        $messages = [];

        foreach ($chat->messages()->with('parts')->orderBy('created_at')->get() as $message) {
            $content = $message->parts
                ->where('type', 'text')
                ->pluck('text_content')
                ->implode("\n");

            if ($content) {
                $messages[] = [
                    'role' => $message->role,
                    'content' => $content,
                ];
            }
        }

        return $messages;
    }

    protected function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'created_at' => $message->created_at,
            'selected_model' => $message->selected_model,
            'attachments' => $message->attachments,
            'parts' => $message->parts->map(function ($part) {
                return [
                    'id' => $part->id,
                    'type' => $part->type,
                    'content' => match ($part->type) {
                        'text' => $part->text_content,
                        'reasoning' => $part->reasoning_content,
                        'file' => [
                            'media_type' => $part->file_media_type,
                            'filename' => $part->file_filename,
                            'url' => $part->file_url,
                        ],
                        default => null,
                    },
                    'tool_name' => $part->tool_name,
                    'tool_state' => $part->tool_state,
                    'tool_input' => $part->tool_input,
                    'tool_output' => $part->tool_output,
                ];
            }),
        ];
    }
}
