<?php

namespace App\Http\Middleware;

use App\Models\App;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Access token is required',
            ], 401);
        }

        $app = App::where('access_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$app) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive access token',
            ], 401);
        }

        // Attach the authenticated app to the request
        $request->merge(['authenticated_app' => $app]);

        return $next($request);
    }
}
