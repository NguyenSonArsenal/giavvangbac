<?php
/**
 * Extract inline CSS/JS from Blade views into external files.
 * Run: php extract-assets.php
 */

$viewBase = __DIR__ . '/resources/views/frontend';
$cssDir = __DIR__ . '/public/frontend/css';
$jsDir = __DIR__ . '/public/frontend/js';

// Ensure directories exist
if (!is_dir($cssDir))
    mkdir($cssDir, 0755, true);
if (!is_dir($jsDir))
    mkdir($jsDir, 0755, true);

// ═══════════════════════════════════════════
// 1. Extract layout base CSS → app.css
// ═══════════════════════════════════════════
$layoutFile = "$viewBase/partials/layout.blade.php";
$layoutContent = file_get_contents($layoutFile);

// Extract the <style> block from layout
preg_match('/<style>(.*?)<\/style>/s', $layoutContent, $layoutStyleMatch);
$layoutCss = trim($layoutStyleMatch[1] ?? '');

if ($layoutCss) {
    file_put_contents("$cssDir/app.css", "/* ═══ GiáVàng.vn — Base Styles ═══ */\n$layoutCss\n");
    echo "✅ Created: public/frontend/css/app.css\n";

    // Replace inline style with link tag in layout
    $layoutContent = preg_replace(
        '/<style>.*?<\/style>/s',
        '<link rel="stylesheet" href="/frontend/css/app.css"/>',
        $layoutContent
    );
    file_put_contents($layoutFile, $layoutContent);
    echo "   Updated layout to use <link> tag\n";
}

// ═══════════════════════════════════════════
// 2. Process each page view
// ═══════════════════════════════════════════
$pages = [
    ['home/index.blade.php', 'home'],
    ['brand/silver.blade.php', 'brand'],
    ['article/bac-999-la-gi.blade.php', 'article-bac999'],
    ['article/nen-mua-bac-o-dau.blade.php', 'article-muabac'],
    ['article/bac-co-phai-kenh-dau-tu-tot.blade.php', 'article-dautu'],
    ['tool/quy-doi-bac.blade.php', 'tool-quydoi'],
    ['tool/so-sanh-gia-bac.blade.php', 'tool-sosanh'],
    ['tool/lich-su-gia-bac.blade.php', 'tool-lichsu'],
    ['stats/index.blade.php', 'stats'],
];

foreach ($pages as [$relPath, $name]) {
    $filePath = "$viewBase/$relPath";
    if (!file_exists($filePath)) {
        echo "⚠️  Skip (not found): $relPath\n";
        continue;
    }

    $content = file_get_contents($filePath);
    $changed = false;

    // ── Extract CSS from @push('styles') ──
    if (preg_match("/@push\('styles'\)\s*<style>(.*?)<\/style>\s*@endpush/s", $content, $cssMatch)) {
        $css = trim($cssMatch[1]);
        if ($css) {
            file_put_contents("$cssDir/{$name}.css", "/* ═══ $name page styles ═══ */\n$css\n");
            echo "✅ Created: public/frontend/css/{$name}.css\n";

            $content = str_replace(
                $cssMatch[0],
                "@push('styles')\n<link rel=\"stylesheet\" href=\"/frontend/css/{$name}.css\"/>\n@endpush",
                $content
            );
            $changed = true;
        }
    }

    // ── Extract JS from @push('scripts') ──
    if (preg_match("/@push\('scripts'\)\s*<script>(.*?)<\/script>\s*@endpush/s", $content, $jsMatch)) {
        $js = trim($jsMatch[1]);
        if ($js) {
            file_put_contents("$jsDir/{$name}.js", "/* ═══ $name page scripts ═══ */\n$js\n");
            echo "✅ Created: public/frontend/js/{$name}.js\n";

            $content = str_replace(
                $jsMatch[0],
                "@push('scripts')\n<script src=\"/frontend/js/{$name}.js\"></script>\n@endpush",
                $content
            );
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents($filePath, $content);
        echo "   Updated: $relPath\n";
    }
}

echo "\n🎉 All done! CSS → public/frontend/css/, JS → public/frontend/js/\n";
