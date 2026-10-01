<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-token auth for the MCP endpoint (/mcp). The token is generated in
 * /admin → Settings → AI (MCP) and only its SHA-256 hash is stored.
 * Also sets the locale context that SetLocale normally provides, since
 * routes/ai.php runs outside the web middleware group.
 */
class McpTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $hash = Setting::get('mcp_token_hash');
        $token = (string) $request->bearerToken();

        if (! $hash || $token === '' || ! hash_equals($hash, hash('sha256', $token))) {
            return response()->json(['error' => 'unauthorized', 'message' => 'Valid Bearer token required (admin → Settings → AI / MCP).'], 401)
                ->header('WWW-Authenticate', 'Bearer realm="winson-mcp"');
        }

        // Remember last use (at most once a minute — avoid a write per call).
        $last = Setting::get('mcp_last_used_at');
        if (! $last || now()->diffInSeconds(\Illuminate\Support\Carbon::parse($last)) > 60) {
            Setting::set('mcp_last_used_at', now()->toIso8601String());
        }

        $locale = config('app.default_locale');
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
