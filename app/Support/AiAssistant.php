<?php

namespace App\Support;

use App\Models\BotKnowledge;
use App\Models\Category;
use App\Models\Product;
use App\Models\TelegramClient;
use App\Models\TelegramConversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * The bot's AI assistant: answers ONLY questions about barcode scanners,
 * data collection terminals and related equipment, in the client's language,
 * as plain text, grounded in the real catalog (site search per question).
 * Provider: any OpenAI-compatible chat API (config('services.ai')).
 */
class AiAssistant
{
    public function __construct(private SiteSearch $search) {}

    public static function configured(): bool
    {
        return config('services.ai.base_url') && config('services.ai.model');
    }

    /** Returns the reply text, or null if the AI is unavailable (caller sends a fallback). */
    public function answer(TelegramClient $client, string $question, ?TelegramConversation $conversation = null): ?AiReply
    {
        if (! self::configured()) {
            return null;
        }

        $locale = self::guessLocale($question);
        // Context = this conversation only (each conversation starts fresh)…
        $history = ($conversation ? $conversation->messages() : $client->messages())->reorder()->latest('id')->take(12)->get()->reverse()
            ->map(fn ($m) => ['role' => $m->role === 'assistant' ? 'assistant' : 'user', 'content' => Str::limit($m->text, 1500)])
            ->values()->all();

        // …plus what we learned last time, so returning clients aren't asked everything again.
        $previous = $client->conversations()->whereNotNull('summary')
            ->when($conversation, fn ($q) => $q->whereKeyNot($conversation->id))->value('summary');
        $system = $this->systemPrompt($question, $locale)
            .($previous ? "\n\n## Summary of this client's previous conversation (for context)\n".Str::limit($previous, 800) : '');

        $messages = array_merge(
            [['role' => 'system', 'content' => $system]],
            $history,
            [['role' => 'user', 'content' => Str::limit($question, 2000)]],
        );

        $text = $this->chat($messages);

        return $text === null ? null : self::parseReply($text);
    }

    /**
     * Pull [[guide:ID]] markers out of the model's text: they become verbatim
     * guides (steps + barcode images) sent by the server. Unknown/inactive ids
     * are dropped, so the model can't make the bot send something that doesn't exist.
     */
    public static function parseReply(string $text): AiReply
    {
        preg_match_all('/\[\[\s*guide\s*:\s*(\d+)\s*\]\]/i', $text, $m);
        $ids = array_values(array_unique(array_map('intval', $m[1])));
        $guides = $ids
            ? BotKnowledge::with('images')->where('kind', 'guide')->where('is_active', true)->whereIn('id', $ids)->get()
                ->sortBy(fn ($g) => array_search($g->id, $ids, true))->values()
            : collect();

        $clean = trim(preg_replace('/\[\[\s*guide\s*:\s*\d+\s*\]\]/i', '', $text));

        return new AiReply(self::plainText($clean), $guides);
    }

    /**
     * Manager-facing summary of a finished conversation (in Russian):
     * ['summary' => string, 'interest' => high|medium|low] or [] if the AI is unavailable.
     */
    public function summarize(TelegramConversation $conversation): array
    {
        if (! self::configured()) {
            return [];
        }

        $transcript = $conversation->messages()->get()
            ->map(fn ($m) => ($m->role === 'assistant' ? 'Assistant: ' : 'Client: ').Str::limit($m->text, 800))
            ->implode("\n");
        if (trim($transcript) === '') {
            return [];
        }

        $text = $this->chat([
            ['role' => 'system', 'content' => <<<'PROMPT'
                You summarize a sales chat between a client and the Winson barcode-equipment assistant for a sales manager.
                Write the summary in Russian, 2–5 short lines: what the client needs (purpose, place, codes, connection, form, volume),
                which Winson models were recommended or discussed, open questions, and the next step for the manager.
                Rate purchase interest: "high" (concrete need, asked about price/availability/quantity or ready to buy),
                "medium" (exploring options for a real need), "low" (general questions / off-topic).
                Answer with JSON only: {"summary": "...", "interest": "high|medium|low"}
                PROMPT],
            ['role' => 'user', 'content' => $transcript],
        ], 400);

        if ($text === null) {
            return [];
        }
        if (preg_match('/\{.*\}/s', $text, $m) && is_array($json = json_decode($m[0], true))) {
            return [
                'summary' => isset($json['summary']) ? Str::limit(trim((string) $json['summary']), 1500) : null,
                'interest' => in_array($json['interest'] ?? null, ['high', 'medium', 'low'], true) ? $json['interest'] : null,
            ];
        }

        return ['summary' => Str::limit(trim($text), 1500), 'interest' => null];
    }

    /** One chat-completions call; returns the text or null (logged) on any failure. */
    private function chat(array $messages, int $maxTokens = 700): ?string
    {
        try {
            $response = Http::timeout(config('services.ai.timeout', 30))
                ->withToken((string) config('services.ai.api_key'))
                ->asJson()
                ->post(rtrim(config('services.ai.base_url'), '/').'/chat/completions', [
                    'model' => config('services.ai.model'),
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => $maxTokens,
                ]);

            $text = $response->json('choices.0.message.content');
            if (! $response->successful() || ! is_string($text) || trim($text) === '') {
                Log::warning('AI call failed', ['status' => $response->status(), 'body' => Str::limit($response->body(), 500)]);

                return null;
            }

            return $text;
        } catch (Throwable $e) {
            Log::warning('AI call error: '.$e->getMessage());

            return null;
        }
    }

    /** Cyrillic → ru (for links/fallbacks); the model itself mirrors the user's language. */
    public static function guessLocale(string $text): string
    {
        return preg_match('/\p{Cyrillic}/u', $text) ? 'ru' : 'uz';
    }

    /** Telegram gets plain text: strip Markdown the model may still produce; stay under 4096 chars. */
    public static function plainText(string $text): string
    {
        $text = preg_replace('/^#{1,6}\s*/m', '', $text);
        $text = preg_replace('/(\*\*|__|`{1,3})/', '', $text);
        $text = preg_replace('/^\s*[*-]\s+/m', '• ', $text);
        $text = preg_replace('/\[([^\]]+)\]\((https?:[^)]+)\)/', '$1: $2', $text);

        return Str::limit(trim($text), 3500);
    }

    /** Catalogs up to this size are given to the model in full; bigger ones → search matches only. */
    private const FULL_CATALOG_LIMIT = 80;

    /** Editable instructions (role, scope, consulting flow, style, practice notes). */
    public const INSTRUCTIONS_FILE = 'resources/ai/telegram-assistant.md';

    private function systemPrompt(string $question, string $locale): string
    {
        app()->setLocale($locale);
        \Illuminate\Support\Facades\URL::defaults(['locale' => $locale]);

        $line = function (Product $p) {
            $specs = FeatureTable::for($p)->flatten(1)->map(fn ($r) => $r[0]->name.': '.$r[1])->implode('; ');
            $desc = Str::limit((string) $p->description, 220);

            return "- {$p->name} [{$p->category?->name}] — {$desc}".($specs ? " | {$specs}" : '').' | '.route('catalog.item', [$p->category, $p]);
        };

        $query = Product::with('category', 'featureValues.feature', 'featureValues.option')->whereHas('category');
        if ((clone $query)->count() <= self::FULL_CATALOG_LIMIT) {
            $catalogTitle = 'FULL CATALOG (all models we sell)';
            $products = $query->orderBy('category_id')->orderBy('sort_order')->get();
        } else {
            $catalogTitle = 'MODELS MATCHING THE QUESTION (site search; there are more in the catalog)';
            $ids = $this->search->search($question, 12)['products']->pluck('id');
            $products = $query->whereIn('id', $ids)->get();
        }
        $catalog = $products->map($line)->implode("\n") ?: '- (no matching models)';

        $categories = Category::withCount('products')->orderBy('sort_order')->get()
            ->map(fn ($c) => "- {$c->name} ({$c->products_count}) — ".route('catalog.category', $c))->implode("\n");

        $contacts = Contacts::all();
        $contactLine = collect([
            $contacts['phone'] ? 'phone '.$contacts['phone'] : null,
            $contacts['email'] ? 'email '.$contacts['email'] : null,
            $contacts['telegram_url'] ? 'Telegram '.$contacts['telegram_url'] : null,
        ])->filter()->implode(', ') ?: 'the "Narx so\'rash / Запросить цену" button on any product page';

        // HTML comments in the .md are notes for editors, not for the model.
        $instructions = preg_replace('/<!--.*?-->/s', '', self::instructions());
        $instructions = str_replace('{contacts}', $contactLine, $instructions);

        return trim($instructions)."\n\n"
            .$this->knowledgeSection($locale)
            ."## Catalog categories\n{$categories}\n\n"
            ."## {$catalogTitle}\n{$catalog}\n\n"
            .'Full catalog: '.route('catalog.index');
    }

    /** Admin-edited copy (survives deploys) if it exists, else the default in the repo. */
    public const INSTRUCTIONS_OVERRIDE = 'app/ai/telegram-assistant.md'; // under storage/

    public static function instructionsPath(): string
    {
        $override = storage_path(self::INSTRUCTIONS_OVERRIDE);

        return is_file($override) ? $override : base_path(self::INSTRUCTIONS_FILE);
    }

    public static function instructionsOverridden(): bool
    {
        return is_file(storage_path(self::INSTRUCTIONS_OVERRIDE));
    }

    /**
     * Taught knowledge (/admin → Chatbot training) + the guide protocol. The
     * protocol lives here, not in the editable .md, so it can't be edited away.
     */
    private function knowledgeSection(string $locale): string
    {
        $items = BotKnowledge::with('products')->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
        if ($items->isEmpty()) {
            return "## Scanner setting guides\nNone yet. If a client asks how to configure a scanner (enable/disable code types, interface, suffix/Enter, beep…), do NOT invent steps or programming barcodes — say there is no instruction for it yet and offer a manager.\n\n";
        }

        $faq = $items->where('kind', 'faq')->map(fn ($k) => '- '.$k->title($locale).' ['.$k->modelsLabel().']: '.Str::limit($k->content($locale), 600))->implode("\n");
        $guides = $items->where('kind', 'guide')->map(fn ($k) => "- [[guide:{$k->id}]] {$k->title_uz} / {$k->title_ru} — models: ".$k->modelsLabel()
            .($k->keywords ? ' — e.g. '.Str::limit(preg_replace('/\s*\R\s*/u', '; ', trim($k->keywords)), 300) : ''))->implode("\n");

        return ($faq ? "## Facts taught by the company (use them, in the client's language)\n{$faq}\n\n" : '')
            ."## Scanner setting guides\n"
            ."Guides contain exact steps and programming barcodes. Protocol:\n"
            ."- When the client asks how to configure/set up a scanner and a guide below matches BOTH the request and the client's model (or is for ALL models), answer with one short sentence and the guide's marker on its own line, e.g. \"Here is how:\\n[[guide:12]]\". The bot replaces the marker with the steps and barcode images.\n"
            ."- If the matching guide is for specific models and the client hasn't said which model they have, ask for the model first (it is printed on the label under the scanner).\n"
            ."- Never write setting steps or programming barcodes yourself and never invent a marker. If no guide matches, say there is no instruction for that yet and offer a manager.\n"
            ."- Use at most 2 markers per answer.\n"
            .($guides ?: '- (no guides yet)')."\n\n";
    }

    /** The instructions file; a minimal built-in fallback if it's missing. */
    public static function instructions(): string
    {
        $path = self::instructionsPath();

        return is_file($path)
            ? (string) file_get_contents($path)
            : 'You are the Winson sales consultant. Only discuss Winson barcode scanning equipment. Reply in the client\'s language, plain text. Never invent prices or specs. Contacts: {contacts}.';
    }
}
