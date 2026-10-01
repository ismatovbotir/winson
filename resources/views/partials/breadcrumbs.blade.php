{{-- Visible breadcrumbs, from the same steps as the BreadcrumbList JSON-LD. --}}
@php $crumbs = \App\Support\Seo::page()->crumbs(); @endphp
@if (count($crumbs) > 1)
    <nav aria-label="{{ __('site.seo.breadcrumbs') }}" class="text-sm">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-ink-soft">
            @foreach ($crumbs as $crumb)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)
                        <span aria-hidden="true" class="text-line">/</span>
                    @endif
                    @if ($crumb['url'] && ! $loop->last)
                        <a href="{{ $crumb['url'] }}" class="font-medium text-accent-ink hover:text-navy">{{ $crumb['name'] }}</a>
                    @else
                        <span @if ($loop->last) aria-current="page" class="text-ink" @endif>{{ $crumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
