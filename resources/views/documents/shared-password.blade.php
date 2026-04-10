<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} — Dot.docs</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-full flex items-center justify-center py-10">
    <div class="w-full max-w-sm px-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-6 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900">Password protected</h1>
                <p class="text-sm text-gray-500 mt-1">Enter the password to view <strong>{{ $document->title }}</strong></p>
            </div>

            <form action="{{ route('documents.shared.unlock', $document->uuid) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" autofocus required
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500
                                  @error('password') border-red-400 @enderror" />
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg py-2 transition">
                    View document
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-gray-400 mt-4">
            <a href="{{ url('/') }}" class="hover:underline">← Dot.docs</a>
        </p>
    </div>
</body>
</html>
