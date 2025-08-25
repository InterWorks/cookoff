<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $apiKeyValue = $request->header('X-API-Key');

        if (!$apiKeyValue) {
            return response()->json(['error' => 'API key required'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify the API key exists and is valid
        $apiKey = ApiKey::verify($apiKeyValue);

        if (!$apiKey || !$apiKey->isValid()) {
            return response()->json(['error' => 'Invalid or expired API key'], Response::HTTP_UNAUTHORIZED);
        }

        // Check permission if specified
        if ($permission && !$apiKey->hasPermission($permission)) {
            return response()->json(['error' => 'Insufficient permissions'], Response::HTTP_FORBIDDEN);
        }

        // Update last used timestamp
        $apiKey->markAsUsed();

        // Add API key to request for potential use in controllers
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
