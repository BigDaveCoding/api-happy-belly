<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() == 404) {
            Log::channel('api_404_response')->notice('API Request Not Found - 404 Status', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
        }

        Log::channel('api_requests')->info('Api Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'headers' => $request->headers->all(),
        ]);

        return $response;
    }
}
