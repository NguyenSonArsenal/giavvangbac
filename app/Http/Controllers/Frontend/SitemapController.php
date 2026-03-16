<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $domain = config('app.url');
        $today = now()->toDateString();

        $urls = [
            // Trang chủ
            ['loc' => '/',                          'priority' => '1.0', 'changefreq' => 'daily',   'lastmod' => $today],
            // Giá bạc theo thương hiệu
            ['loc' => '/gia-bac-phu-quy',           'priority' => '0.8', 'changefreq' => 'daily',   'lastmod' => $today],
            ['loc' => '/gia-bac-ancarat',            'priority' => '0.8', 'changefreq' => 'daily',   'lastmod' => $today],
            ['loc' => '/gia-bac-doji',               'priority' => '0.8', 'changefreq' => 'daily',   'lastmod' => $today],
            ['loc' => '/gia-bac-kim-ngan-phuc',      'priority' => '0.8', 'changefreq' => 'daily',   'lastmod' => $today],
            // Công cụ
            ['loc' => '/quy-doi-bac',                'priority' => '0.7', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => '/so-sanh-gia-bac',            'priority' => '0.7', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => '/lich-su-gia-bac',            'priority' => '0.7', 'changefreq' => 'weekly',  'lastmod' => $today],
            // Bài viết tĩnh
            ['loc' => '/bac-999-la-gi',              'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => '/nen-mua-bac-o-dau',          'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => '/bac-co-phai-kenh-dau-tu-tot', 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            // Danh sách bài viết
            ['loc' => '/bai-viet',                   'priority' => '0.6', 'changefreq' => 'daily',   'lastmod' => $today],
        ];

        // Dynamic blog posts
        $posts = Post::select('slug', 'updated_at')->orderBy('updated_at', 'desc')->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc'        => '/bai-viet/' . $post->slug,
                'priority'   => '0.5',
                'changefreq' => 'weekly',
                'lastmod'    => $post->updated_at->toDateString(),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$domain}{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}

