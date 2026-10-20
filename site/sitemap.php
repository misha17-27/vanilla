<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/blog.php';
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

// Запись карты: адрес без языка, дата изменения, как часто меняется, важность
// и фото (по нему приходят из Google Картинок). Порядок — как у argflex.co.uk:
// страницы, разделы, товары, статьи.
$entries = [];
$add = function (string $path, string $mod, string $freq, string $prio, string $img = '') use (&$entries) {
    $entries[] = compact('path', 'mod', 'freq', 'prio', 'img');
};

$add('/',                  $pageStamp('index.php'),                                      'daily',   '1.0');
$add('/bolme/bento-tort/', $day(max($dataStamp, (int)@filemtime(__DIR__ . '/bento.php'))), 'weekly',  '0.9');
$add('/bolme/cake-to-go/', $day(max($dataStamp, (int)@filemtime(__DIR__ . '/cake-to-go.php'))), 'weekly', '0.9');
foreach (own_categories() as $c) {
    $add(cat_path($c), $day($dataStamp), 'weekly', '0.9');
}
$add('/konstruktor/',      $pageStamp('konstruktor.php'), 'monthly', '0.8');
$add('/terkibler/',        $pageStamp('fillings.php'),    'monthly', '0.7');
$add('/reyler/',           $pageStamp('reviews.php'),     'weekly',  '0.6');
$add('/faq/',              $pageStamp('faq.php'),         'monthly', '0.5');
$add('/haqqimizda/',       $pageStamp('about.php'),       'monthly', '0.5');
$add('/elaqe/',            $pageStamp('contact.php'),     'monthly', '0.5');

// Блог: список статей меняется с каждой новой статьёй
$posts = blog_posts();
$blogLast = '';
foreach ($posts as $bp) $blogLast = max($blogLast, blog_updated($bp));
$add('/blog/', $blogLast !== '' ? max($blogLast, $pageStamp('blog.php')) : $pageStamp('blog.php'), 'weekly', '0.7');

// У товара своя дата: когда его правили, иначе когда добавили. Одинаковый
// lastmod у всех страниц поиск быстро перестаёт учитывать.
foreach ($PRODUCTS as $p) {
    $ts = (int)($p['updated'] ?? 0) ?: (int)($p['created'] ?? 0);
    $add('/mehsul/' . $p['slug'] . '/', $ts ? $day($ts) : $day($dataStamp), 'weekly', '0.8', (string)($p['img'] ?? ''));
}
// Статьи: дата последней правки и обложка
foreach ($posts as $bp) {
    $add(blog_path($bp), blog_updated($bp), 'monthly', '0.6', asset((string)$bp['image']));
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"'
   . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($entries as $en) {
    // Каждая языковая версия — отдельная запись со полным набором альтернатив,
    // как требует справка Google по hreflang в карте сайта.
    foreach ($LANGS as $cur) {
        echo '  <url>' . "\n";
        echo '    <loc>' . e(CANON_HOST . lang_path($cur, $en['path'])) . '</loc>' . "\n";
        echo '    <lastmod>' . $en['mod'] . '</lastmod>' . "\n";
        echo '    <changefreq>' . $en['freq'] . '</changefreq>' . "\n";
        echo '    <priority>' . $en['prio'] . '</priority>' . "\n";
        foreach ($LANGS as $l) {
            echo '    <xhtml:link rel="alternate" hreflang="' . e($l) . '" href="' . e(CANON_HOST . lang_path($l, $en['path'])) . '"/>' . "\n";
        }
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e(CANON_HOST . lang_path('ru', $en['path'])) . '"/>' . "\n";
        if ($en['img'] !== '') {
            echo '    <image:image><image:loc>' . e(CANON_HOST . asset($en['img'])) . '</image:loc></image:image>' . "\n";
        }
        echo '  </url>' . "\n";
    }
}
echo '</urlset>';
