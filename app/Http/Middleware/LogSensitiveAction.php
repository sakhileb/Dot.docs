<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSensitiveAction
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->user()) {
            return $response;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->currentTeam?->id,
            'action' => $request->method(),
            'route_name' => $routeName,
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'metadata' => [
                'query' => $request->query(),
                'route_parameters' => $route?->parameters() ?? [],
            ],
        ]);

        return $response;
    }
}
