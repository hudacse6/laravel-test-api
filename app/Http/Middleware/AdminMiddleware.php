<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role === 0) {
            return $next($request);
        } else {
            return response()->json([
                'success' => false,
                "message" => "unauthenticated"
            ]);
        }
    }
}
