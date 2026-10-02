<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatVoiceController extends Controller
{
    /**
     * Upload an audio voice note recording.
     */
    public function upload(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Avtorizatsiyadan o\'ting.'], 401);
        }

        $request->validate([
            'audio'    => 'required|file|max:25600', // max 25MB
            'duration' => 'nullable|numeric|min:0|max:600',
        ]);

        $file = $request->file('audio');
        $rawExt = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getMimeType() ?? '');

        // Determine appropriate audio extension
        $ext = 'webm';
        if (in_array($rawExt, ['webm', 'ogg', 'mp3', 'wav', 'm4a', 'aac', 'mp4'])) {
            $ext = $rawExt;
        } elseif (str_contains($mime, 'mp4') || str_contains($mime, 'm4a') || str_contains($mime, 'aac')) {
            $ext = 'm4a';
        } elseif (str_contains($mime, 'ogg')) {
            $ext = 'ogg';
        } elseif (str_contains($mime, 'wav')) {
            $ext = 'wav';
        } elseif (str_contains($mime, 'mp3') || str_contains($mime, 'mpeg')) {
            $ext = 'mp3';
        }

        $filename = 'voice_' . Auth::id() . '_' . time() . '_' . Str::random(8) . '.' . $ext;
        $path = $file->storeAs('voice_messages', $filename, 'public');

        return response()->json([
            'success'  => true,
            'path'     => $path,
            'url'      => asset('storage/' . $path),
            'duration' => (int) round((float) $request->input('duration', 0)),
        ]);
    }
}
