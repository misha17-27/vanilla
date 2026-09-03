<?php
require_once __DIR__ . '/includes/config.php';
$LANG_BASE = '';   // адреса тут всегда без языковой приставки
header('Content-Type: application/xml; charset=UTF-8');

// Дата изменения: для каталога — когда его последний раз правили в админке,
// для остальных страниц — свежайшая из шаблона, текстов и SEO.
$dataStamp = max(
    (int)@filemtime(CATALOG_FILE),
    (int)@filemtime(__DIR__ . '/data/categories.json')
);
$textStamp = max(
    (int)@filemtime(__DIR__ . '/data/texts.json'),
    (int)@filemtime(__DIR__ . '/data/seo.json')
);
$day = fn(int $ts): string => date('Y-m-d', $ts ?: time());

$pageStamp = function (string $file) use ($textStamp): string {
    $ts = max((int)@filemtime(__DIR__ . '/' . $file), $textStamp);
    return date('Y-m-d', $ts ?: time());
};

// Список страниц: адрес без языка + дата изменения
$entries = [
    ['/',                   $pageStamp('index.php')],
    ['/bolme/bento-tort/',  $day(max($dataStamp, (int)@filemtime(__DIR__ . '/bento.php')))],
    ['/bolme/cake-to-go/',  $day(max($dataStamp, (int)@filemtime(__DIR__ . '/cake-to-go.php')))],
    ['/terkibler/',         $pageStamp('fillings.php')],
    ['/reyler/',            $pageStamp('reviews.php')],
    ['/konstruktor/',       $pageStamp('konstruktor.php')],
    ['/haqqimizda/',        $pageStamp('about.php')],
    ['/faq/',               $pageStamp('faq.php')],
    ['/elaqe/',             $pageStamp('contact.php')],
];
foreach (own_categories() as $c) {
    $entries[] = [cat_path($c), $day($dataStamp)];
}
foreach ($PRODUCTS as $p) {
    $entries[] = ['/mehsul/' . $p['slug'] . '/', $day($dataStamp)];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($entries as [$path, $mod]) {
    // Каждая языковая версия — отдельная запись со полным набором альтернатив,
    // как требует справка Google по hreflang в карте сайта.
    foreach ($LANGS as $cur) {
        echo '  <url>' . "\n";
        echo '    <loc>' . e(CANON_HOST . lang_path($cur, $path)) . '</loc>' . "\n";
        echo '    <lastmod>' . $mod . '</lastmod>' . "\n";
        foreach ($LANGS as $l) {
            echo '    <xhtml:link rel="alternate" hreflang="' . e($l) . '" href="' . e(CANON_HOST . lang_path($l, $path)) . '"/>' . "\n";
        }
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e(CANON_HOST . lang_path('ru', $path)) . '"/>' . "\n";
        echo '  </url>' . "\n";
    }
}
echo '</urlset>';
