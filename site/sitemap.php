<?php
require __DIR__ . '/includes/config.php';
header('Content-Type: application/xml; charset=UTF-8');
$urls = ['/', '/bolme/bento-tort/', '/bolme/cake-to-go/', '/terkibler/', '/reyler/', '/haqqimizda/', '/faq/', '/elaqe/'];
foreach ($PRODUCTS as $p) {
    $urls[] = product_url($p);
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . e(CANON_HOST . $u) . '</loc></url>' . "\n";
}
echo '</urlset>';
