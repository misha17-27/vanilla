<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/xml; charset=UTF-8');
$urls = ['/', '/bolme/bento-tort/', '/bolme/cake-to-go/', '/terkibler/', '/reyler/', '/konstruktor/', '/haqqimizda/', '/faq/', '/elaqe/'];
foreach (own_categories() as $c) {
    $urls[] = cat_url($c);
}
foreach ($PRODUCTS as $p) {
    $urls[] = product_url($p);
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($urls as $u) {
    echo '  <url>' . "\n";
    echo '    <loc>' . e(CANON_HOST . $u) . '</loc>' . "\n";
    foreach ($LANGS as $l) {
        $href = CANON_HOST . $u . ($l === 'ru' ? '' : '?lang=' . $l);
        echo '    <xhtml:link rel="alternate" hreflang="' . e($l) . '" href="' . e($href) . '"/>' . "\n";
    }
    echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e(CANON_HOST . $u) . '"/>' . "\n";
    echo '  </url>' . "\n";
}
echo '</urlset>';
