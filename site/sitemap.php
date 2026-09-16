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
// У товара своя дата: когда его правили, иначе когда добавили. Одинаковый
// lastmod у всех страниц поиск быстро перестаёт учитывать.
foreach ($PRODUCTS as $p) {
    $ts = (int)($p['updated'] ?? 0) ?: (int)($p['created'] ?? 0);
    // третьим элементом — фотография торта: по ней приходят из Google Картинок
    $entries[] = ['/mehsul/' . $p['slug'] . '/', $ts ? $day($ts) : $day($dataStamp), $p['img'] ?? ''];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"'
   . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($entries as $entry) {
    [$path, $mod] = $entry;
    $img = $entry[2] ?? '';
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
        if ($img !== '') {
            echo '    <image:image><image:loc>' . e(CANON_HOST . asset($img)) . '</image:loc></image:image>' . "\n";
        }
        echo '  </url>' . "\n";
    }
}
echo '</urlset>';
