@extends('admin.layout')

@section('title', $client->displayName())

@section('content')
    @include('admin.partials.page-header', ['title' => $client->displayName()])

    <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
        <div class="space-y-6">
            @forelse ($client->conversations as $conversation)
                <x-admin.card>
                    <div class="-mt-1 mb-4 flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-line pb-3 text-sm">
                        <span class="font-semibold text-navy">{{ __('admin.tg_clients.conversation') }} #{{ $conversation->id }}</span>
                        <span class="font-mono text-xs text-ink-soft">{{ $conversation->started_at->format('d.m.Y H:i') }} → {{ $conversation->ended_at?->format('H:i') ?? __('admin.tg_clients.open') }}</span>
                        @if ($conversation->end_reason)
                            <span class="rounded-full bg-canvas-alt px-2 py-0.5 text-xs text-ink-soft">{{ __('admin.tg_clients.end_reasons.'.$conversation->end_reason) }}</span>
                        @endif
                        @if ($conversation->interest)
                            <span @class(['rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-red-50 text-red-700' => $conversation->interest === 'high',
                                'bg-amber-50 text-amber-700' => $conversation->interest === 'medium',
                                'bg-canvas-alt text-ink-soft' => $conversation->interest === 'low'])>{{ __('admin.tg_clients.interest.'.$conversation->interest) }}</span>
                        @endif
                        @if ($conversation->rating)<span class="text-xs">{{ str_repeat('⭐', $conversation->rating) }}</span>@endif
                        @if ($conversation->lead)
                            <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-accent-ink hover:text-navy">{{ __('admin.tg_clients.lead_created') }}</a>
                        @elseif ($conversation->wants_contact === false)
                            <span class="text-xs text-ink-soft">{{ __('admin.tg_clients.no_contact') }}</span>
                        @endif
                    </div>
                    @if ($conversation->summary)
                        <div class="mb-4 whitespace-pre-line rounded-lg border-l-4 border-accent bg-accent-soft/50 p-3 text-sm text-ink">{{ $conversation->summary }}</div>
                    @endif
                    <div class="space-y-3">
                        @foreach ($conversation->messages as $m)
                            <div @class(['flex', 'justify-end' => $m->role === 'user'])>
                                <div @class([
                                    'max-w-[85%] whitespace-pre-line rounded-2xl px-4 py-2.5 text-sm',
                                    'rounded-br-sm bg-navy text-white' => $m->role === 'user',
                                    'rounded-bl-sm bg-canvas-alt text-ink' => $m->role !== 'user',
                                ])>{{ $m->text }}<span class="mt-1 block text-right font-mono text-[10px] opacity-60">{{ $m->created_at?->format('d.m H:i') }}</span></div>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @empty
                <x-admin.card><p class="text-sm text-ink-soft">{{ __('admin.tg_clients.no_messages') }}</p></x-admin.card>
            @endforelse
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-ink-soft">{{ __('admin.tg_clients.phone') }}</dt><dd class="font-mono">{{ $client->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-soft">Telegram</dt><dd>
                        @if ($client->username)<a href="https://t.me/{{ $client->username }}" target="_blank" rel="noopener" class="text-accent-ink hover:text-navy">{{ '@'.$client->username }}</a>@else <span class="font-mono text-xs">id {{ $client->telegram_user_id }}</span>@endif
                    </dd></div>
                    <div><dt class="text-ink-soft">{{ __('admin.tg_clients.registered') }}</dt><dd>{{ $client->registered_at?->format('d.m.Y H:i') ?? __('admin.tg_clients.not_registered') }}</dd></div>
                    <div><dt class="text-ink-soft">{{ __('admin.tg_clients.last_seen') }}</dt><dd>{{ $client->last_seen_at?->format('d.m.Y H:i') }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('admin.telegram-clients.update', $client) }}" class="mt-4 border-t border-line pt-4">
                    @csrf
                    @method('PATCH')
                    <label class="flex items-center gap-2 text-sm text-red-600">
                        <input type="hidden" name="is_blocked" value="0">
                        <input type="checkbox" name="is_blocked" value="1" @checked($client->is_blocked) onchange="this.form.submit()" class="rounded border-line">
                        {{ __('admin.tg_clients.block') }}
                    </label>
                </form>
            </x-admin.card>

            @if ($client->leads->isNotEmpty())
                <x-admin.card :title="__('admin.nav.leads')">
                    <ul class="space-y-2 text-sm">
                        @foreach ($client->leads as $lead)
                            <li>{{ $lead->created_at->format('d.m.Y') }} — {{ $lead->product?->name ?? '—' }} <span class="text-xs text-ink-soft">({{ __('admin.leads.statuses.'.$lead->status) }})</span></li>
                        @endforeach
                    </ul>
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
