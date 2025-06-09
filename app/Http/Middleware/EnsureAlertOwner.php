<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAlertOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $alert = $request->route('alert');

        if (!$alert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($alert->email !== $user->email) {
            return response()->json(['message' => 'You are not authorized to access this alert'], 403);
        }

        return $next($request);
    }
}
