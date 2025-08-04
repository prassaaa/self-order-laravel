<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $duration = 300): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Don't cache authenticated requests with user-specific data
        if ($request->user() && $this->isUserSpecificRoute($request)) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($request);

        // Try to get cached response
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Cache', 'HIT');
        }

        // Process request
        $response = $next($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            $cacheData = [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $this->getHeadersToCache($response),
            ];

            Cache::put($cacheKey, $cacheData, $duration);
            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }

    /**
     * Generate cache key for the request.
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = 'response_cache:' . $request->getPathInfo();
        
        // Include query parameters in cache key
        if ($request->getQueryString()) {
            $key .= ':' . md5($request->getQueryString());
        }

        // Include user role for role-specific caching
        if ($request->user()) {
            $roles = $request->user()->getRoleNames()->implode(',');
            $key .= ':role:' . md5($roles);
        }

        return $key;
    }

    /**
     * Check if route contains user-specific data.
     */
    protected function isUserSpecificRoute(Request $request): bool
    {
        $userSpecificRoutes = [
            '/api/orders', // User's orders
            '/api/payments', // User's payments
            '/api/profile', // User profile
        ];

        $path = $request->getPathInfo();
        
        foreach ($userSpecificRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get headers that should be cached.
     */
    protected function getHeadersToCache(Response $response): array
    {
        $headersToCache = [
            'Content-Type',
            'Content-Encoding',
            'Vary',
        ];

        $headers = [];
        foreach ($headersToCache as $header) {
            if ($response->headers->has($header)) {
                $headers[$header] = $response->headers->get($header);
            }
        }

        return $headers;
    }
}
