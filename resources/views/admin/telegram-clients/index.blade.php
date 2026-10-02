@extends('admin.layout')

@section('title', __('admin.tg_clients.title'))

@section('content')
    @include('admin.partials.page-header', ['title' => __('admin.tg_clients.title'), 'subtitle' => __('admin.tg_clients.subtitle')])

    <form method="GET" class="mb-4 flex max-w-md gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('admin.tg_clients.search') }}"
            class="w-full rounded-md border border-line bg-white px-3 py-2 text-sm shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
        <button class="rounded-md border border-line bg-white px-3 text-sm">{{ __('site.search.submit') }}</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-4 py-3">{{ __('admin.tg_clients.client') }}</th>
                    <th class="px-4 py-3">{{ __('admin.tg_clients.phone') }}</th>
                    <th class="hidden px-4 py-3 md:table-cell">{{ __('admin.tg_clients.messages') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('admin.tg_clients.last_seen') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($clients as $client)
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.telegram-clients.show', $client) }}" class="font-medium text-navy hover:text-accent-ink">{{ $client->displayName() }}</a>
                            <p class="text-xs text-ink-soft">
                                @if ($client->username){{ '@'.$client->username }} · @endif{{ $client->language_code }}
                                @if (! $client->isRegistered()) · <span class="text-amber-600">{{ __('admin.tg_clients.not_registered') }}</span>@endif
                                @if ($client->is_blocked) · <span class="text-red-600">{{ __('admin.tg_clients.blocked') }}</span>@endif
                            </p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $client->phone ?: '—' }}</td>
                        <td class="hidden px-4 py-3 font-mono text-xs text-ink-soft md:table-cell">{{ $client->messages_count }} · {{ __('admin.tg_clients.leads_n', ['n' => $client->leads_count]) }}</td>
                        <td class="hidden whitespace-nowrap px-4 py-3 text-xs text-ink-soft sm:table-cell">{{ $client->last_seen_at?->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-ink-soft">{{ __('admin.tg_clients.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $clients->links() }}</div>
@endsection
