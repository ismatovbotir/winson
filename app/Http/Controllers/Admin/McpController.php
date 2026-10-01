<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Token + permissions for the MCP endpoint (routes/ai.php). */
class McpController extends Controller
{
    /** New token: shown to the admin once, only its SHA-256 hash is stored. */
    public function token()
    {
        $token = 'wsn_'.Str::random(48);
        Setting::set('mcp_token_hash', hash('sha256', $token));
        Setting::set('mcp_token_created_at', now()->toIso8601String());
        Setting::set('mcp_last_used_at', null);

        return redirect()->to(route('admin.settings.edit').'#mcp')
            ->with('mcp_token', $token)
            ->with('status', __('admin.mcp.token_created'));
    }

    public function revoke()
    {
        foreach (['mcp_token_hash', 'mcp_token_created_at', 'mcp_last_used_at'] as $key) {
            Setting::set($key, null);
        }

        return redirect()->to(route('admin.settings.edit').'#mcp')->with('status', __('admin.mcp.token_revoked'));
    }

    public function permissions(Request $request)
    {
        Setting::set('mcp_write_enabled', $request->boolean('write') ? '1' : '0');

        return redirect()->to(route('admin.settings.edit').'#mcp')->with('status', __('admin.common.saved'));
    }
}
