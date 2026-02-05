<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        $file = $request->file('file');
        $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/' . $request->user()->id, $storedName);

        $fileUpload = FileUpload::create([
            'user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => 'local',
        ]);

        return response()->json([
            'id' => $fileUpload->id,
            'name' => $fileUpload->original_name,
            'url' => route('api.files.show', $fileUpload->id),
            'mime_type' => $fileUpload->mime_type,
            'size' => $fileUpload->size,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $file = FileUpload::findOrFail($id);

        // Check if user owns the file or it's part of a public chat
        if ($file->user_id !== $request->user()?->id) {
            abort(403);
        }

        return Storage::disk($file->disk)->download(
            $file->path,
            $file->original_name
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $file = FileUpload::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
