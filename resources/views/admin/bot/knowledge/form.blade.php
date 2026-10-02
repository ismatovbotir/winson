@extends('admin.layout')

@php
    $isGuide = old('kind', $item->kind) === 'guide';
    $title = $item->exists ? __('admin.bot.edit') : ($isGuide ? __('admin.bot.new_guide') : __('admin.bot.new_faq'));
    $selected = collect(old('products', $item->exists ? $item->products->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all();
    $input = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30';
@endphp

@section('title', $title)

@section('content')
    @include('admin.partials.page-header', ['title' => $title])
    @include('admin.bot._tabs')

    <form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.bot-knowledge.update', $item) : route('admin.bot-knowledge.store') }}" class="space-y-6">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <x-admin.card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 [&>*]:min-w-0">
                <div class="flex flex-col gap-1.5">
                    <label for="f_kind" class="text-sm font-medium text-ink">{{ __('admin.bot.kind') }}</label>
                    <select id="f_kind" name="kind" class="{{ $input }}" data-kind>
                        @foreach (\App\Models\BotKnowledge::KINDS as $k)
                            <option value="{{ $k }}" @selected(old('kind', $item->kind) === $k)>{{ __('admin.bot.kinds.'.$k) }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-soft">{{ __('admin.bot.kind_hint') }}</p>
                </div>
                <div class="flex flex-col gap-3 pt-6">
                    <label class="flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active)) class="rounded border-line"> {{ __('admin.bot.active') }}</label>
                </div>

                <x-admin.field name="title_uz" :label="__('admin.bot.title_field').' (UZ)'" :value="$item->title_uz" :hint="__('admin.bot.title_hint')" required />
                <x-admin.field name="title_ru" :label="__('admin.bot.title_field').' (RU)'" :value="$item->title_ru" required />

                <x-admin.field name="content_uz" :rows="9" :label="__('admin.bot.content').' (UZ)'" :value="$item->content_uz" :hint="__('admin.bot.content_hint')" required />
                <x-admin.field name="content_ru" :rows="9" :label="__('admin.bot.content').' (RU)'" :value="$item->content_ru" required />

                <x-admin.field class="sm:col-span-2" name="keywords" :rows="3" :label="__('admin.bot.keywords')" :value="$item->keywords" :hint="__('admin.bot.keywords_hint')" />
            </div>
        </x-admin.card>

        <x-admin.card :title="__('admin.bot.models')">
            <label class="mb-3 flex items-center gap-2 text-sm font-medium">
                <input type="hidden" name="applies_to_all" value="0">
                <input type="checkbox" name="applies_to_all" value="1" @checked(old('applies_to_all', $item->applies_to_all)) class="rounded border-line" data-all-models>
                {{ __('admin.bot.all_models') }}
            </label>
            <div data-model-list class="grid gap-x-6 gap-y-1.5 sm:grid-cols-2">
                @foreach ($products->groupBy(fn ($p) => $p->category?->name) as $cat => $group)
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-wide text-ink-soft sm:col-span-2">{{ $cat }}</p>
                    @foreach ($group as $p)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="products[]" value="{{ $p->id }}" @checked(in_array($p->id, $selected, true)) class="rounded border-line"> {{ $p->name }}</label>
                    @endforeach
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-soft">{{ __('admin.bot.models_hint') }}</p>
        </x-admin.card>

        <x-admin.card :title="__('admin.bot.images')" data-guide-only>
            <p class="-mt-3 mb-4 text-xs text-ink-soft">{{ __('admin.bot.images_hint') }}</p>
            @if ($item->exists && $item->images->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($item->images as $img)
                        <div class="grid items-start gap-3 rounded-lg border border-line p-3 sm:grid-cols-[140px_1fr_80px] has-[:checked]:opacity-50">
                            <img src="{{ $img->url }}" alt="" class="w-full rounded bg-white object-contain">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input type="text" name="images[{{ $img->id }}][caption_uz]" value="{{ $img->caption_uz }}" placeholder="{{ __('admin.bot.caption') }} (UZ)" class="{{ $input }}">
                                <input type="text" name="images[{{ $img->id }}][caption_ru]" value="{{ $img->caption_ru }}" placeholder="{{ __('admin.bot.caption') }} (RU)" class="{{ $input }}">
                                <label class="flex items-center gap-2 text-sm text-red-600"><input type="hidden" name="images[{{ $img->id }}][remove]" value="0"><input type="checkbox" name="images[{{ $img->id }}][remove]" value="1" class="rounded border-line"> {{ __('admin.common.delete') }}</label>
                            </div>
                            <label class="text-xs text-ink-soft">{{ __('admin.bot.order') }}<input type="number" min="0" name="images[{{ $img->id }}][sort_order]" value="{{ $img->sort_order }}" class="{{ $input }} mt-1"></label>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mt-4 flex flex-col gap-1.5">
                <label for="f_new_images" class="text-sm font-medium text-ink">{{ __('admin.bot.add_images') }}</label>
                <input id="f_new_images" name="new_images[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp"
                    class="min-w-0 max-w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-soft">
            </div>
        </x-admin.card>

        @include('admin.partials.form-actions', ['cancel' => route('admin.bot-knowledge.index')])
    </form>

    <script>
        (() => {
            const kind = document.querySelector('[data-kind]');
            const all = document.querySelector('[data-all-models]');
            const sync = () => {
                document.querySelectorAll('[data-guide-only]').forEach((el) => (el.hidden = kind.value !== 'guide'));
                document.querySelector('[data-model-list]').classList.toggle('opacity-40', all.checked);
                document.querySelectorAll('[data-model-list] input').forEach((i) => (i.disabled = all.checked));
            };
            kind.addEventListener('change', sync);
            all.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
