<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserModelPreference;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function __construct(
        protected AIService $aiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $models = $this->aiService->getAvailableModels();
        $user = $request->user();

        if ($user) {
            $preferences = UserModelPreference::where('user_id', $user->id)
                ->pluck('enabled', 'model_id')
                ->toArray();

            $models = array_map(function ($model) use ($preferences) {
                $model['enabled'] = $preferences[$model['id']] ?? true;
                return $model;
            }, $models);
        }

        return response()->json($models);
    }

    public function updatePreference(Request $request, string $modelId): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        UserModelPreference::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'model_id' => $modelId,
            ],
            [
                'enabled' => $validated['enabled'],
            ]
        );

        return response()->json(['message' => 'Preference updated']);
    }
}
