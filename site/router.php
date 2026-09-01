<?php
// Front controller: URL-структура повторяет vanilla.az (WordPress), чтобы сохранить SEO.
// Dev:  php -S 127.0.0.1:8123 -t site site/router.php
// Prod: Apache + .htaccess переписывает все не-файловые запросы сюда.

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Служебные каталоги и скрытые файлы наружу не отдаём
if (preg_match('#^/(uploads/ratelimit/|data/|includes/|lang/)#', $uri) || str_contains($uri, '/.')) {
    http_response_code(404);
    exit;
}

// Под php -S: существующие файлы отдаёт сам сервер
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $uri;
    if ($uri !== '/' && is_file($file)) {
        return false;
    }
}

// sitemap
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    exit;
}

// админ-панель: /admin, /admin/products, /admin/settings …
if ($uri === '/admin' || str_starts_with($uri, '/admin/')) {
    if (!preg_match('#^/admin/assets/#', $uri)) {
        require __DIR__ . '/admin/index.php';
        exit;
    }
}

// нормализуем завершающий слэш
$path = $uri === '/' ? '/' : rtrim($uri, '/') . '/';

// Спам-страницы, оставшиеся от WordPress: убираем из индекса совсем
if (preg_match('#^/melbet-#i', $path)) {
    http_response_code(410);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "410 Gone";
    exit;
}

// Старые карты сайта (sitemap_index.xml и файлы Yoast) — на нашу
if (preg_match('#^/(sitemap_index|page-sitemap|product-sitemap|product_cat-sitemap|product_tag-sitemap|pa_[a-z]+-sitemap)\.xml$#i', $uri)) {
    header('Location: /sitemap.xml', true, 301);
    exit;
}

// Русская ветка старого сайта: /ru/mehsul/… → /mehsul/…
if (preg_match('#^/ru(/.*)?$#', $path, $m)) {
    $rest = $m[1] ?? '/';
    header('Location: ' . ($rest === '/' ? '/' : $rest) . '?lang=ru', true, 301);
    exit;
}

// 301-редиректы со старых WP-адресов, которые мы не переносим
$redirects = [
    '/tortlar/'      => '/bolme/bento-tort/',
    '/video/'        => 'https://www.instagram.com/vanilla_cake_az/',
    '/instagram/'    => '/',
    '/my-account/'   => '/',
    '/cart/'         => '/',
    '/checkout/'     => '/',
    '/track-order/'  => '/',
    '/wishlist/'     => '/',
];
if (isset($redirects[$path])) {
    header('Location: ' . $redirects[$path], true, 301);
    exit;
}
foreach (['/biskvit/', '/terkib/', '/olcu/'] as $prefix) {
    if (strpos($path, $prefix) === 0) {
        header('Location: /terkibler/', true, 301);
        exit;
    }
}

// Страницы категорий каталога: адрес берём из самой категории
require_once __DIR__ . '/includes/config.php';
foreach (categories() as $catKey => $catRow) {
    if (($catRow['page'] ?? '') === 'own' && cat_url($catRow) === $path) {
        $CAT_KEY = $catKey;
        require __DIR__ . '/category.php';
        exit;
    }
}

// Метки товаров WooCommerce (/product-tag/…) — в подходящий раздел
if (preg_match('#^/product-tag/([^/]+)/$#', $path, $m)) {
    $tagMap = [
        'mini-tort'    => '/bolme/cake-to-go/',
        'bento-bantik' => '/bolme/bento-tort/bento-bantik-tort/',
    ];
    header('Location: ' . ($tagMap[$m[1]] ?? '/bolme/bento-tort/'), true, 301);
    exit;
}

// Подкатегории старого каталога: /bolme/bento-tort/usaq-tortlari/ и т.п.
if (preg_match('#^/bolme/bento-tort/([^/]+)/$#', $path, $m) && $m[1] !== 'page') {
    $subMap = [];
    header('Location: ' . ($subMap[$m[1]] ?? '/bolme/bento-tort/'), true, 301);
    exit;
}
if (preg_match('#^/bolme/cake-to-go/[^/]+/$#', $path)) {
    header('Location: /bolme/cake-to-go/', true, 301);
    exit;
}

// маршруты
$routes = [
    '/'                    => 'index.php',
    '/bolme/bento-tort/'   => 'bento.php',
    '/bolme/cake-to-go/'   => 'cake-to-go.php',
    '/terkibler/'          => 'fillings.php',
    '/reyler/'             => 'reviews.php',
    '/konstruktor/'        => 'konstruktor.php',
    '/haqqimizda/'         => 'about.php',
    '/faq/'                => 'faq.php',
    '/elaqe/'              => 'contact.php',
];

if (preg_match('#^/bolme/bento-tort/page/\d+/$#', $path)) {
    header('Location: /bolme/bento-tort/', true, 301); // каталог теперь на одной странице
    exit;
}

// канонический редирект на вариант со слэшем
if ($uri !== $path && (isset($routes[$path]) || preg_match('#^/mehsul/[a-z0-9-]+/$#', $path))) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $path . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
}

if (isset($routes[$path])) {
    require __DIR__ . '/' . $routes[$path];
    exit;
}

if (preg_match('#^/mehsul/([a-z0-9_-]+)/$#i', $path, $m)) {
    $ROUTE_SLUG = $m[1];
    require __DIR__ . '/mehsul.php';
    exit;
}

// 404
http_response_code(404);
$page = '404';
require_once __DIR__ . '/includes/config.php';
$page_title = 'Vanilla Cake — 404';
$page_meta  = '';
require __DIR__ . '/includes/header.php';
echo '<section class="page-hero"><div class="container"><h1>404</h1><p class="lead">' . e($t['nf_text'] ?? 'Səhifə tapılmadı') . '</p>'
   . '<p style="margin-top:24px"><a class="btn btn-primary" href="/">' . e($t['nav_home']) . '</a></p></div></section>';
require __DIR__ . '/includes/footer.php';
