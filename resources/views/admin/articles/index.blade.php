@extends('admin.layout')

@section('title', __('admin.articles.title'))

@section('content')
    @include('admin.partials.page-header', [
        'title' => __('admin.articles.title'),
        'action' => ['url' => route('admin.articles.create'), 'label' => __('admin.articles.create')],
    ])

    <div class="overflow-x-auto rounded-xl border border-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-4 py-3">{{ __('admin.articles.title_field') }}</th>
                    <th class="hidden px-4 py-3 sm:table-cell">{{ __('admin.articles.published_at') }}</th>
                    <th class="hidden px-4 py-3 text-right sm:table-cell">{{ __('admin.statistics.views_30d') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($articles as $article)
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($article->image_url)
                                    <img src="{{ $article->image_url }}" alt="" class="hidden h-9 w-16 shrink-0 sm:block rounded bg-navy object-cover">
                                @endif
                                <div>
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="font-medium text-navy hover:text-accent-ink">{{ $article->title_uz }}</a>
                                    <p class="text-xs text-ink-soft">{{ $article->title_ru }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden whitespace-nowrap px-4 py-3 font-mono text-xs text-ink-soft sm:table-cell">{{ $article->published_at?->format('d.m.Y') }}</td>
                        <td class="hidden px-4 py-3 text-right font-mono tabular-nums text-ink sm:table-cell">{{ $article->views_30d }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="mr-3 text-sm font-medium text-accent-ink hover:text-navy">{{ __('admin.common.edit') }}</a>
                            @include('admin.partials.delete-button', ['action' => route('admin.articles.destroy', $article)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-ink-soft">{{ __('admin.common.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
