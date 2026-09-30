{{-- Top-N viewed models. Expects $title, $rows (model/views/visitors), $link and $label closures. --}}
<x-admin.card :title="$title">
    @if ($rows->isEmpty())
        <p class="px-5 pb-5 text-sm text-ink-soft sm:px-6">{{ __('admin.statistics.empty') }}</p>
    @else
        <table class="w-full text-left text-sm">
            <thead class="border-y border-line bg-canvas-alt text-xs uppercase tracking-wide text-ink-soft">
                <tr>
                    <th class="px-4 py-2.5 sm:px-6">{{ __('admin.common.name') }}</th>
                    <th class="px-4 py-2.5 text-right">{{ __('admin.statistics.views') }}</th>
                    <th class="hidden px-4 py-2.5 text-right sm:table-cell sm:pr-6">{{ __('admin.statistics.visitors') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($rows as $row)
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-2.5 sm:px-6">
                            <a href="{{ $link($row['model']) }}" class="font-medium text-navy hover:text-accent-ink">{{ $label($row['model']) }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono tabular-nums text-ink">{{ number_format($row['views'], 0, '.', ' ') }}</td>
                        <td class="hidden px-4 py-2.5 text-right font-mono tabular-nums text-ink-soft sm:table-cell sm:pr-6">{{ number_format($row['visitors'], 0, '.', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-admin.card>
