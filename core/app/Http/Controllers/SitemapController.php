<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
class SitemapController extends Controller
{
    

    public function index()
    {
        // Ping only once per cache duration
        if (!Cache::has('sitemap_pinged')) {
            $sitemapUrl = urlencode(url('/sitemap.xml'));
            try {
                Http::get("https://www.google.com/ping?sitemap={$sitemapUrl}");
                Http::get("https://www.bing.com/ping?sitemap={$sitemapUrl}");
                Cache::put('sitemap_pinged', true, 3600); // Only ping once per hour
            } catch (\Exception $e) {
                \Log::warning("Failed to ping search engines: " . $e->getMessage());
            }
        }

        $sitemap = Cache::remember('sitemap.xml', 3600, function () {
            $urls = [];
            $urls[] = url('/');
            $urls[] = url('/about');
            $urls[] = url('/contact');

            $products = Product::where('is_published', 1)->latest()->get();

            foreach ($products as $product) {
                $urls[] = route('product.detail', $product->slug);
            }

            return view('sitemap.xml', compact('urls'))->render();
        });

        return response($sitemap, 200)->header('Content-Type', 'application/xml');
    }
}
