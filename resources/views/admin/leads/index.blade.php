@extends('admin.layout')

@section('title', __('admin.leads.title'))

@section('content')
    @include('admin.partials.page-header', ['title' => __('admin.leads.title'), 'subtitle' => __('admin.leads.subtitle')])

    <nav class="mb-4 flex flex-wrap gap-1 rounded-lg border border-line bg-white p-1 shadow-sm sm:inline-flex">
        <a href="{{ route('admin.leads.index') }}" @class(['rounded-md px-3 py-1.5 text-sm font-medium', 'bg-navy text-white' => ! $status, 'text-ink-soft hover:bg-canvas-alt' => $status])>
            {{ __('admin.leads.all') }} <span class="font-mono text-xs opacity-70">{{ $counts->sum() }}</span>
        </a>
        @foreach (\App\Models\Lead::STATUSES as $s)
            <a href="{{ route('admin.leads.index', ['status' => $s]) }}" @class(['rounded-md px-3 py-1.5 text-sm font-medium', 'bg-navy text-white' => $status === $s, 'text-ink-soft hover:bg-canvas-alt' => $status !== $s])>
                {{ __('admin.leads.statuses.'.$s) }} <span class="font-mono text-xs opacity-70">{{ $counts[$s] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>

    @if ($leads->isEmpty())
        <x-admin.card><p class="text-sm text-ink-soft">{{ __('admin.leads.empty') }}</p></x-admin.card>
    @else
        <div class="space-y-3">
            @foreach ($leads as $lead)
                <div @class(['rounded-xl border bg-white p-4 shadow-sm', 'border-accent' => $lead->status === 'new', 'border-line' => $lead->status !== 'new'])>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-navy">
                                {{ $lead->name ?: '—' }}
                                @if ($lead->telegram_username)
                                    <a href="https://t.me/{{ $lead->telegram_username }}" target="_blank" rel="noopener" class="ml-1 text-sm font-normal text-accent-ink hover:text-navy">{{ '@'.$lead->telegram_username }}</a>
                                @endif
                            </p>
                            <p class="mt-0.5 text-sm text-ink-soft">
                                @if ($lead->phone)<a href="tel:{{ $lead->phone }}" class="font-mono text-ink hover:text-accent-ink">{{ $lead->phone }}</a> · @endif
                                {{ $lead->created_at->format('d.m.Y H:i') }} · {{ strtoupper((string) $lead->locale) }} · {{ $lead->source }}
                                @if ($lead->client) · <a href="{{ route('admin.telegram-clients.show', $lead->client) }}" class="text-accent-ink hover:text-navy">{{ __('admin.leads.client') }}</a>@endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="rounded-md border border-line bg-white px-2 py-1 text-sm">
                                @foreach (\App\Models\Lead::STATUSES as $s)
                                    <option value="{{ $s }}" @selected($lead->status === $s)>{{ __('admin.leads.statuses.'.$s) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @if ($lead->product)
                        <p class="mt-2 text-sm">📦 <a href="{{ route('admin.products.edit', $lead->product) }}" class="font-medium text-navy hover:text-accent-ink">{{ $lead->product->name }}</a></p>
                    @endif
                    @if ($lead->message)
                        <p class="mt-2 whitespace-pre-line rounded-lg bg-canvas p-3 text-sm text-ink">{{ $lead->message }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $leads->links() }}</div>
    @endif
@endsection
