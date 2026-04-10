<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} — Dot.docs</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-full py-10">
    <div class="max-w-4xl mx-auto px-6">
        <div class="mb-6">
            <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:underline">← Dot.docs</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $document->title }}</h1>
            <p class="text-sm text-gray-400 mb-8">
                By {{ $document->owner->name }} &middot; Last updated {{ $document->updated_at->diffForHumans() }}
            </p>

            <div class="prose prose-lg max-w-none">
                {!! $document->content !!}
            </div>
        </div>
    </div>
</body>
</html>
