{{-- AI / MCP access (separate forms — outside the main settings form). --}}
@php
    $endpoint = url('/mcp');
    $hasToken = (bool) \App\Models\Setting::get('mcp_token_hash');
    $created = \App\Models\Setting::get('mcp_token_created_at');
    $lastUsed = \App\Models\Setting::get('mcp_last_used_at');
    $writeOn = \App\Models\Setting::get('mcp_write_enabled') === '1';
    $newToken = session('mcp_token');
    $fmt = fn ($iso) => $iso ? \Illuminate\Support\Carbon::parse($iso)->format('d.m.Y H:i') : '—';
@endphp

<x-admin.card :title="__('admin.mcp.title')" id="mcp" class="mt-6 scroll-mt-6">
    <p class="-mt-3 mb-5 text-xs text-ink-soft">{{ __('admin.mcp.hint') }}</p>

    <div class="grid gap-3 rounded-lg border border-line bg-canvas p-4 text-sm sm:grid-cols-2">
        <p class="sm:col-span-2"><span class="text-ink-soft">{{ __('admin.mcp.endpoint') }}:</span>
            <code class="ml-1 select-all rounded bg-white px-1.5 py-0.5 font-mono text-navy">{{ $endpoint }}</code></p>
        <p><span class="text-ink-soft">{{ __('admin.mcp.status') }}:</span>
            @if ($hasToken)
                <span class="font-semibold text-accent-ink">{{ __('admin.mcp.active') }}</span>
                <span class="text-xs text-ink-soft">({{ __('admin.mcp.created') }} {{ $fmt($created) }})</span>
            @else
                <span class="font-semibold text-ink-soft">{{ __('admin.mcp.off') }}</span>
            @endif
        </p>
        <p><span class="text-ink-soft">{{ __('admin.mcp.last_used') }}:</span> {{ $fmt($lastUsed) }}</p>
    </div>

    @if ($newToken)
        <div class="mt-5 rounded-lg border-2 border-accent bg-accent-soft p-4 text-sm">
            <p class="font-semibold text-navy">{{ __('admin.mcp.copy_now') }}</p>
            <code class="mt-2 block select-all break-all rounded bg-white px-3 py-2 font-mono text-navy">{{ $newToken }}</code>

            <p class="mt-4 font-medium text-navy">Claude Code:</p>
            <pre class="mt-1 overflow-x-auto whitespace-pre rounded bg-navy-deep px-3 py-2 font-mono text-xs text-canvas select-all">claude mcp add --transport http winson {{ $endpoint }} --header "Authorization: Bearer {{ $newToken }}"</pre>

            <p class="mt-4 font-medium text-navy">Claude Desktop (claude_desktop_config.json):</p>
            <pre class="mt-1 overflow-x-auto whitespace-pre rounded bg-navy-deep px-3 py-2 font-mono text-xs text-canvas select-all">{
  "mcpServers": {
    "winson": {
      "command": "npx",
      "args": ["-y", "mcp-remote", "{{ $endpoint }}", "--header", "Authorization: Bearer {{ $newToken }}"]
    }
  }
}</pre>
            <p class="mt-3 text-xs text-ink-soft">{{ __('admin.mcp.copy_hint') }}</p>
        </div>
    @endif

    <div class="mt-5 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('admin.mcp.token') }}"
            @if ($hasToken) onsubmit="return confirm(@js(__('admin.mcp.regenerate_confirm')))" @endif>
            @csrf
            <button class="rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-soft">
                {{ $hasToken ? __('admin.mcp.regenerate') : __('admin.mcp.generate') }}
            </button>
        </form>
        @if ($hasToken)
            <form method="POST" action="{{ route('admin.mcp.revoke') }}" onsubmit="return confirm(@js(__('admin.mcp.revoke_confirm')))">
                @csrf
                @method('DELETE')
                <button class="text-sm font-medium text-red-600 hover:text-red-800">{{ __('admin.mcp.revoke') }}</button>
            </form>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.mcp.permissions') }}" class="mt-6 border-t border-line pt-5">
        @csrf
        @method('PUT')
        <label class="flex items-start gap-3 text-sm">
            <input type="hidden" name="write" value="0">
            <input type="checkbox" name="write" value="1" @checked($writeOn) onchange="this.form.submit()" class="mt-0.5 rounded border-line">
            <span>
                <span class="font-medium text-ink">{{ __('admin.mcp.write') }}</span>
                <span class="mt-0.5 block text-xs text-ink-soft">{{ __('admin.mcp.write_hint') }}</span>
            </span>
        </label>
        <noscript><button class="mt-2 text-sm font-semibold text-accent-ink">{{ __('admin.common.save') }}</button></noscript>
    </form>
</x-admin.card>
