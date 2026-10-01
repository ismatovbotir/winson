<?php

namespace App\Mcp\Tools;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feature;
use App\Models\PageView;
use App\Models\Product;
use App\Models\Setting;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Name('site_overview')]
#[Description('Start here. Summary of the Winson site: what it is, content counts, languages, URLs, last 30 days traffic, whether write tools are enabled.')]
class SiteOverview extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::json([
            'site' => 'Winson — bilingual (Uzbek uz / Russian ru, no English) catalog of Winson barcode scanners, data collection terminals and smart terminals for Uzbekistan. Lead-gen: no prices or cart; every product funnels to "request a price".',
            'home' => ['uz' => route('home', ['locale' => 'uz']), 'ru' => route('home', ['locale' => 'ru'])],
            'counts' => [
                'categories' => Category::count(),
                'products' => Product::count(),
                'articles' => Article::count(),
                'features' => Feature::count(),
            ],
            'traffic_30d' => [
                'views' => PageView::since(30)->count(),
                'visitors' => PageView::since(30)->distinct()->count('visitor_hash'),
            ],
            'contacts_filled' => (bool) (Setting::get('contact_phone') || Setting::get('contact_email')),
            'write_tools_enabled' => Setting::get('mcp_write_enabled') === '1',
            'rules' => [
                'Always write both uz and ru texts.',
                'Never invent prices, specs or certifications — only use facts you were given.',
                'Uzbek uses Latin script; Russian uses Cyrillic.',
            ],
        ]);
    }
}
