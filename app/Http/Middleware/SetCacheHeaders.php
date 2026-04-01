<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->getMethod() === 'GET') {
            // Cache static assets for 1 year
            if ($request->is('build/*', 'images/*', 'fonts/*', 'css/*', 'js/*')) {
                $response
                    ->header('Cache-Control', 'public, max-age=31536000, immutable')
                    ->header('ETag', md5($response->getContent()));
            }
            // Cache API responses for 5 minutes
            elseif ($request->is('api/*')) {
                $response
                    ->header('Cache-Control', 'private, max-age=300')
                    ->header('ETag', md5($response->getContent()));
            }
            // Don't cache HTML pages
            else {
                $response
                    ->header('Cache-Control', 'private, no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }
        }

        return $response;
    }
}
