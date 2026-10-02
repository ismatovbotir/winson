{{-- Telegram bot / Mini App / AI status. Secrets live in .env — never shown here. --}}
@php
    $tg = config('services.telegram');
    $ai = config('services.ai');
    $row = fn ($ok) => $ok ? '<span class="font-semibold text-accent-ink">✓</span>' : '<span class="font-semibold text-red-600">✗</span>';
@endphp
<x-admin.card :title="__('admin.telegram.title')" id="telegram" class="mt-6 scroll-mt-6">
    <p class="-mt-3 mb-5 text-xs text-ink-soft">{{ __('admin.telegram.hint') }}</p>

    <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
        <div class="flex gap-2"><dd>{!! $row((bool) $tg['bot_token']) !!}</dd><dt>TELEGRAM_BOT_TOKEN</dt></div>
        <div class="flex gap-2"><dd>{!! $row((bool) $tg['bot_username']) !!}</dd><dt>TELEGRAM_BOT_USERNAME
            @if ($tg['bot_username']) — <a href="https://t.me/{{ ltrim($tg['bot_username'], '@') }}" target="_blank" rel="noopener" class="text-accent-ink hover:text-navy">{{ '@'.ltrim($tg['bot_username'], '@') }}</a>@endif</dt></div>
        <div class="flex gap-2"><dd>{!! $row((bool) $tg['admin_chat_id']) !!}</dd><dt>TELEGRAM_ADMIN_CHAT_ID <span class="text-xs text-ink-soft">({{ __('admin.telegram.admin_chat_hint') }})</span></dt></div>
        <div class="flex gap-2"><dd>{!! $row((bool) ($ai['base_url'] && $ai['model'])) !!}</dd><dt>AI: <span class="font-mono text-xs">{{ $ai['model'] ?: '—' }}</span> {{ $ai['api_key'] ? '' : __('admin.telegram.no_ai_key') }}</dt></div>
    </dl>

    <div class="mt-5 space-y-2 rounded-lg border border-line bg-canvas p-4 text-sm">
        <p><span class="text-ink-soft">{{ __('admin.telegram.mini_app') }}:</span> <code class="select-all font-mono text-navy">{{ route('tg.entry') }}</code></p>
        <p><span class="text-ink-soft">Webhook:</span> <code class="select-all font-mono text-navy">{{ route('telegram.webhook') }}</code></p>
        <p><span class="text-ink-soft">{{ __('admin.telegram.setup') }}:</span> <code class="select-all font-mono text-navy">php artisan telegram:setup</code></p>
        <p><span class="text-ink-soft">{{ __('admin.telegram.instructions') }}:</span> <code class="select-all font-mono text-navy">{{ \App\Support\AiAssistant::INSTRUCTIONS_FILE }}</code></p>
    </div>
</x-admin.card>
