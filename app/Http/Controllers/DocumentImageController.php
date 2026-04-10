<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentImageController extends Controller
{
    public function store(Request $request, string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $document);

        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $path = $request->file('image')->store('document-images', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
