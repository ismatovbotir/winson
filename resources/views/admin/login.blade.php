@extends('admin.layout')

@section('title', __('admin.login.title'))

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-navy-deep px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex justify-center">
                <span class="rounded-lg bg-white px-4 py-2"><img src="{{ asset('images/logo/winson-logo.png') }}" alt="Winson" class="h-8 w-auto"></span>
            </div>

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-xl">
                @csrf
                <h1 class="text-lg font-bold text-navy">{{ __('admin.login.title') }}</h1>

                <x-admin.field name="email" type="email" :label="__('admin.login.email')" required />
                <x-admin.field name="password" type="password" :label="__('admin.login.password')" required />

                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="remember" value="1" class="rounded border-line">
                    {{ __('admin.login.remember') }}
                </label>

                <button type="submit" class="w-full rounded-md bg-navy px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-navy-soft">
                    {{ __('admin.login.submit') }}
                </button>
            </form>

            <div class="mt-4 flex justify-center gap-2 text-xs">
                @foreach (config('app.supported_locales', ['uz', 'ru']) as $code)
                    <a href="{{ route('locale.switch', $code) }}"
                        class="rounded px-2 py-1 font-semibold {{ app()->getLocale() === $code ? 'bg-accent text-navy-deep' : 'text-canvas/70 hover:text-white' }}">
                        {{ strtoupper($code) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
