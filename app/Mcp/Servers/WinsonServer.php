<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\WriteArticle;
use App\Mcp\Tools;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Winson site')]
#[Version('1.0.0')]
#[Instructions(<<<'MD'
    Tools for the Winson website (barcode scanners, data collection terminals, smart terminals; Uzbekistan).
    The site is bilingual Uzbek (uz, Latin script) + Russian (ru) — never English — and has no prices:
    every product funnels to "request a price".

    Start with site_overview. For analysis use get_statistics and find_content_gaps.
    Before creating content, check what exists (list_*, search_site) and read list_features before setting product characteristics.
    Always provide both uz and ru texts. Never invent prices, specs, stock or certifications.
    Write tools (create_*/update_*) appear only when the site owner enabled changes in admin → Settings → AI / MCP.
    There are no delete tools; images are uploaded in the admin panel.
    MD)]
class WinsonServer extends Server
{
    /** All tools on one page — some clients don't follow tools/list cursors. */
    public int $defaultPaginationLength = 50;

    protected array $tools = [
        Tools\SiteOverview::class,
        Tools\GetStatistics::class,
        Tools\FindContentGaps::class,
        Tools\SearchSite::class,
        Tools\ListCategories::class,
        Tools\ListProducts::class,
        Tools\GetProduct::class,
        Tools\ListArticles::class,
        Tools\GetArticle::class,
        Tools\ListFeatures::class,
        // Write tools (registered only when enabled in admin):
        Tools\CreateCategory::class,
        Tools\UpdateCategory::class,
        Tools\CreateProduct::class,
        Tools\UpdateProduct::class,
        Tools\CreateArticle::class,
        Tools\UpdateArticle::class,
    ];

    protected array $prompts = [
        WriteArticle::class,
    ];
}
