<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $documents = Document::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'kind' => ['required', 'in:text,code,sheet'],
            'language' => ['nullable', 'string', 'max:50'],
            'message_id' => ['nullable', 'uuid', 'exists:messages,id'],
        ]);

        $document = Document::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json($document, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $document = Document::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($document);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $document = Document::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'nullable', 'string'],
            'kind' => ['sometimes', 'in:text,code,sheet'],
            'language' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $document->update($validated);

        return response()->json($document);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $document = Document::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $document->delete();

        return response()->json(['message' => 'Document deleted']);
    }
}
