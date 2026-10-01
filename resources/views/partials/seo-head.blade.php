{{-- SEO <head> tags. Page views fill App\Support\Seo before the layout renders. --}}
@php
    $seo = \App\Support\Seo::page();
    $alternates = $seo->alternates();
    $locale = app()->getLocale();
@endphp

<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->metaDescription() }}">
@if ($seo->isNoindex())
    <meta name="robots" content="noindex, follow">
@else
    <link rel="canonical" href="{{ $seo->canonical() }}">
@endif

@foreach ($alternates as $code => $href)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
@endforeach
@if ($alternates)
    <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('app.default_locale')] ?? reset($alternates) }}">
@endif

{{-- Open Graph / Twitter (Telegram link previews use these). --}}
<meta property="og:site_name" content="{{ \App\Support\Seo::SITE_NAME }}">
<meta property="og:type" content="{{ $seo->ogType() }}">
<meta property="og:title" content="{{ $seo->fullTitle() }}">
<meta property="og:description" content="{{ $seo->metaDescription() }}">
<meta property="og:url" content="{{ $seo->canonical() }}">
<meta property="og:image" content="{{ $seo->imageUrl() }}">
<meta property="og:locale" content="{{ $seo->ogLocale($locale) }}">
@foreach (array_keys($alternates) as $code)
    @if ($code !== $locale)
        <meta property="og:locale:alternate" content="{{ $seo->ogLocale($code) }}">
    @endif
@endforeach
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->metaDescription() }}">
<meta name="twitter:image" content="{{ $seo->imageUrl() }}">

{{-- Favicons (generated from the Winson brand colors). --}}
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#123a66">

<script type="application/ld+json">{!! $seo->jsonLd() !!}</script>
