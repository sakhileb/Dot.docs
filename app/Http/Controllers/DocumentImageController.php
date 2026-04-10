<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class DocumentImageController extends Controller
{
    public function store(Request $request, string $uuid): JsonResponse
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $document);

        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $filename = Str::uuid() . '.webp';
        $encoded  = Image::read($request->file('image'))->toWebp(quality: 82);

        Storage::disk('public')->put('document-images/' . $filename, $encoded->toString());

        return response()->json([
            'url' => asset('storage/document-images/' . $filename),
        ]);
    }
}
