@extends('admin.layout')

@section('title', __('admin.bot.title'))

@section('content')
    @include('admin.partials.page-header', ['title' => __('admin.bot.title'), 'subtitle' => __('admin.bot.subtitle')])
    @include('admin.bot._tabs')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-1 text-sm">
            <a href="{{ route('admin.bot-knowledge.index') }}" @class(['rounded-md px-3 py-1.5', 'bg-canvas-alt font-semibold text-navy' => ! $kind, 'text-ink-soft' => $kind])>{{ __('admin.bot.all') }}</a>
            @foreach (\App\Models\BotKnowledge::KINDS as $k)
                <a href="{{ route('admin.bot-knowledge.index', ['kind' => $k]) }}" @class(['rounded-md px-3 py-1.5', 'bg-canvas-alt font-semibold text-navy' => $kind === $k, 'text-ink-soft' => $kind !== $k])>{{ __('admin.bot.kinds.'.$k) }}</a>
            @endforeach
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.bot-knowledge.create', ['kind' => 'guide']) }}" class="rounded-md bg-accent px-4 py-2 text-sm font-semibold text-navy-deep hover:brightness-105">{{ __('admin.bot.new_guide') }}</a>
            <a href="{{ route('admin.bot-knowledge.create', ['kind' => 'faq']) }}" class="rounded-md border border-line bg-white px-4 py-2 text-sm font-semibold text-navy hover:border-accent">{{ __('admin.bot.new_faq') }}</a>
        </div>
    </div>

    @if ($items->isEmpty())
        <x-admin.card>
            <p class="text-sm text-ink-soft">{{ __('admin.bot.empty') }}</p>
            <p class="mt-2 text-sm text-ink-soft">{{ __('admin.bot.empty_hint') }}</p>
        </x-admin.card>
    @else
        <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-line">
                    @foreach ($items as $item)
                        <tr class="hover:bg-canvas">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span @class(['rounded px-1.5 py-0.5 font-mono text-[10px] uppercase', 'bg-accent-soft text-accent-ink' => $item->kind === 'guide', 'bg-canvas-alt text-ink-soft' => $item->kind === 'faq'])>{{ __('admin.bot.kinds.'.$item->kind) }}</span>
                                    <a href="{{ route('admin.bot-knowledge.edit', $item) }}" class="font-medium text-navy hover:text-accent-ink">{{ $item->title_uz }}</a>
                                    @unless ($item->is_active)<span class="text-xs text-ink-soft">({{ __('admin.bot.inactive') }})</span>@endunless
                                </div>
                                <p class="mt-0.5 text-xs text-ink-soft">{{ $item->applies_to_all ? __('admin.bot.all_models') : ($item->products->pluck('name_uz')->implode(', ') ?: '—') }}</p>
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3 text-xs text-ink-soft sm:table-cell">
                                @if ($item->kind === 'guide')🖼 {{ $item->images_count }} · @endif{{ __('admin.bot.sent_n', ['n' => $item->times_sent]) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.bot-knowledge.edit', $item) }}" class="mr-3 text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                                @include('admin.partials.delete-button', ['action' => route('admin.bot-knowledge.destroy', $item)])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
