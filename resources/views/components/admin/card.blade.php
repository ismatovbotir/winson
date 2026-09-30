@props(['title' => null])

<section {{ $attributes->class('rounded-xl border border-line bg-white p-5 shadow-sm sm:p-6') }}>
    @if ($title)
        <h2 class="mb-5 text-base font-semibold text-navy">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>
