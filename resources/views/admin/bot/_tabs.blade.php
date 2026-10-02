<nav class="mb-6 flex flex-wrap gap-1 rounded-lg border border-line bg-white p-1 shadow-sm sm:inline-flex">
    @foreach (['admin.bot-knowledge.index' => ['bot.tab_knowledge', 'admin.bot-knowledge.*'], 'admin.bot.instructions' => ['bot.tab_instructions', 'admin.bot.instructions*'], 'admin.bot.playground' => ['bot.tab_playground', 'admin.bot.playground*']] as $route => [$label, $pattern])
        <a href="{{ route($route) }}" @class(['rounded-md px-3 py-1.5 text-sm font-medium transition', 'bg-navy text-white' => request()->routeIs($pattern), 'text-ink-soft hover:bg-canvas-alt hover:text-navy' => ! request()->routeIs($pattern)])>{{ __('admin.'.$label) }}</a>
    @endforeach
</nav>
