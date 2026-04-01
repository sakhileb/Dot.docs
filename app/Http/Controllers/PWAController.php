<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class PWAController extends Controller
{
    public function manifestJson()
    {
        $manifest = config('pwa');

        return Response::json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function serviceWorker()
    {
        return response()->file(public_path('service-worker.js'), [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=3600',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
