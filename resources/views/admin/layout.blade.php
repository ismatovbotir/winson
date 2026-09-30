<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', __('admin.panel')) — Winson</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased">
        @auth
            @php
                $nav = [
                    'admin.dashboard' => ['admin.nav.dashboard', 'admin.dashboard'],
                    'admin.statistics' => ['admin.nav.statistics', 'admin.statistics'],
                    'admin.categories.index' => ['admin.nav.categories', 'admin.categories.*'],
                    'admin.products.index' => ['admin.nav.products', 'admin.products.*'],
                    'admin.articles.index' => ['admin.nav.articles', 'admin.articles.*'],
                    'admin.hero.edit' => ['admin.nav.hero', 'admin.hero.*'],
                    'admin.banners.index' => ['admin.nav.banners', 'admin.banners.*'],
                    'admin.menu.edit' => ['admin.nav.menu', 'admin.menu.*'],
                    'admin.page-content.edit' => ['admin.nav.page_content', 'admin.page-content.*'],
                    'admin.settings.edit' => ['admin.nav.settings', 'admin.settings.*'],
                ];
            @endphp

            <div class="lg:flex">
                <aside class="bg-navy-deep text-canvas lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-60 lg:shrink-0 lg:flex-col">
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                            <span class="rounded bg-white px-2 py-1"><img src="{{ asset('images/logo/winson-logo.png') }}" alt="Winson" class="h-5 w-auto"></span>
                            <span class="font-mono text-xs uppercase tracking-widest text-accent">CMS</span>
                        </a>
                        <div class="flex gap-1 text-xs">
                            @foreach (config('app.supported_locales', ['uz', 'ru']) as $code)
                                <a href="{{ route('locale.switch', $code) }}"
                                    class="rounded px-1.5 py-0.5 font-semibold {{ app()->getLocale() === $code ? 'bg-accent text-navy-deep' : 'text-canvas/70 hover:text-white' }}">
                                    {{ strtoupper($code) }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <nav class="flex gap-1 overflow-x-auto px-3 pb-3 lg:flex-1 lg:flex-col lg:overflow-visible">
                        @foreach ($nav as $route => [$label, $pattern])
                            <a href="{{ route($route) }}"
                                @class([
                                    'whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition',
                                    'bg-white/10 text-white' => request()->routeIs($pattern),
                                    'text-canvas/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs($pattern),
                                ])>
                                {{ __($label) }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="hidden border-t border-white/10 px-3 py-3 lg:block">
                        <a href="{{ url('/') }}" target="_blank" class="block rounded-md px-3 py-2 text-sm text-canvas/70 hover:text-white">
                            {{ __('admin.nav.view_site') }}
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="w-full rounded-md px-3 py-2 text-left text-sm text-canvas/70 hover:text-white">
                                {{ __('admin.nav.logout') }} ({{ auth()->user()->email }})
                            </button>
                        </form>
                    </div>
                </aside>

                <main class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-8">
                    <div class="mx-auto max-w-5xl">
                        @if (session('status'))
                            <div class="mb-6 rounded-lg border border-accent/40 bg-accent-soft px-4 py-3 text-sm font-medium text-accent-ink" role="status">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                <ul class="list-inside list-disc space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </div>

                    <div class="mx-auto mt-10 flex max-w-5xl justify-between border-t border-line pt-4 text-sm lg:hidden">
                        <a href="{{ url('/') }}" target="_blank" class="text-ink-soft hover:text-navy">{{ __('admin.nav.view_site') }}</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="text-ink-soft hover:text-navy">{{ __('admin.nav.logout') }}</button>
                        </form>
                    </div>
                </main>
            </div>
        @else
            @yield('content')
        @endauth
    </body>
</html>
