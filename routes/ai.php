<?php

use App\Http\Middleware\McpTokenAuth;
use App\Mcp\Servers\WinsonServer;
use Laravel\Mcp\Facades\Mcp;

/*
| MCP endpoint for AI assistants (Claude etc.): analytics + content tools.
| Loaded by laravel/mcp outside the web group (no session/CSRF). Protected by
| a Bearer token managed in /admin → Settings → AI / MCP; write tools only
| appear when "allow changes" is enabled there.
*/
Mcp::web('/mcp', WinsonServer::class)
    ->middleware([McpTokenAuth::class, 'throttle:60,1'])
    ->name('mcp');
