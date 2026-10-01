<?php

namespace App\Mcp\Tools;

use App\Models\Setting;

/**
 * Write tools only exist on the server when "allow changes" is on in
 * /admin → Settings → AI / MCP (read-only by default).
 */
trait WritesContent
{
    public function shouldRegister(): bool
    {
        return Setting::get('mcp_write_enabled') === '1';
    }
}
