<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $domain = 'https://giavangbac.io.vn';

        $urls = [
            // Trang chủ
            ['loc' => '/',                          'priority' => '1.0', 'changefreq' => 'daily'],
            // Giá bạc theo thương hiệu
            ['loc' => '/gia-bac-phu-quy',           'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => '/gia-bac-ancarat',            'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => '/gia-bac-doji',               'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => '/gia-bac-kim-ngan-phuc',      'priority' => '0.8', 'changefreq' => 'daily'],
            // Công cụ
            ['loc' => '/quy-doi-bac',                'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/so-sanh-gia-bac',            'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/lich-su-gia-bac',            'priority' => '0.7', 'changefreq' => 'weekly'],
            // Bài viết
            ['loc' => '/bac-999-la-gi',              'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/nen-mua-bac-o-dau',          'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/bac-co-phai-kenh-dau-tu-tot', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$domain}{$url['loc']}</loc>\n";
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
