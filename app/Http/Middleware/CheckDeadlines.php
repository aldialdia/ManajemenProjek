<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckDeadlines
{
    /**
     * Handle an incoming request.
     * Runs deadline check once per hour via cache lock.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run once per hour using cache
        $cacheKey = 'deadline_check_last_run';
        
        if (!Cache::has($cacheKey)) {
            // Set cache first to prevent multiple simultaneous runs
            Cache::put($cacheKey, now(), now()->addHour());
            
            // Run the deadline check command in background
            try {
                Artisan::call('app:check-task-deadlines');
            } catch (\Exception $e) {
                // Log error but don't interrupt the request
                \Log::error('Deadline check failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
