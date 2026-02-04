<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::where('user_id', $request->user()->id)
            ->withCount('chats')
            ->orderBy('name')
            ->get();

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_color' => ['nullable', 'string', 'max:20'],
        ]);

        $project = Project::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json($project, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $project = Project::where('user_id', $request->user()->id)
            ->withCount('chats')
            ->findOrFail($id);

        return response()->json($project);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $project = Project::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:50'],
            'icon_color' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $project = Project::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }

    public function chats(Request $request, string $id): JsonResponse
    {
        $project = Project::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $chats = $project->chats()
            ->withCount('messages')
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->paginate($request->get('per_page', 50));

        return response()->json($chats);
    }
}
