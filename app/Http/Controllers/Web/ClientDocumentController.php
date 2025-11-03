<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientDocumentController extends Controller
{
    /**
     * Store or replace a client document.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'Client', 403);

        $data = $request->validate([
            'document_type' => ['required', 'in:id_front,id_back,health_card,medical_certificate'],
            'document_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $type = $data['document_type'];
        $file = $data['document_file'];

        $directory = 'documents/' . $user->id;
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        $document = UserDocument::firstOrNew([
            'user_id' => $user->id,
            'type' => $type,
        ]);

        if ($document->exists && $document->path) {
            Storage::disk('public')->delete($document->path);
        }

        $document->fill([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Documento caricato correttamente.'),
                'document' => [
                    'id' => $document->id,
                    'type' => $document->type,
                    'original_name' => $document->original_name,
                    'originalName' => $document->original_name,
                    'url' => Storage::disk('public')->url($document->path),
                    'download_url' => Storage::disk('public')->url($document->path),
                    'downloadUrl' => Storage::disk('public')->url($document->path),
                    'uploaded_at' => optional($document->updated_at)->toIso8601String(),
                    'uploadedAtDisplay' => optional($document->updated_at)->translatedFormat('d/m/Y H:i'),
                ],
            ]);
        }

        return back()->with('status', 'Documento caricato correttamente.');
    }
}
