<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy">{{ $title }}</h1>
        @isset($subtitle)
            <p class="mt-1 text-sm text-ink-soft">{{ $subtitle }}</p>
        @endisset
    </div>
    @isset($action)
        <a href="{{ $action['url'] }}"
            class="rounded-md bg-accent px-4 py-2 text-sm font-semibold text-navy-deep shadow-sm transition hover:brightness-105">
            {{ $action['label'] }}
        </a>
    @endisset
</div>
