<?php
// Front controller: URL-структура повторяет vanilla.az (WordPress), чтобы сохранить SEO.
// Dev:  php -S 127.0.0.1:8123 -t site site/router.php
// Prod: Apache + .htaccess переписывает все не-файловые запросы сюда.

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

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

// нормализуем завершающий слэш
$path = $uri === '/' ? '/' : rtrim($uri, '/') . '/';

// 301-редиректы со старых WP-адресов, которые мы не переносим
$redirects = [
    '/tortlar/'    => '/bolme/bento-tort/',
    '/reyler/'     => '/',
    '/video/'      => 'https://www.instagram.com/vanilla_cake_az/',
    '/my-account/' => '/',
    '/cart/'       => '/',
    '/wishlist/'   => '/',
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

// маршруты
$routes = [
    '/'                    => 'index.php',
    '/bolme/bento-tort/'   => 'bento.php',
    '/bolme/cake-to-go/'   => 'cake-to-go.php',
    '/terkibler/'          => 'fillings.php',
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
require __DIR__ . '/includes/config.php';
$page_title = 'Vanilla Cake — 404';
$page_meta  = '';
require __DIR__ . '/includes/header.php';
echo '<section class="page-hero"><div class="container"><h1>404</h1><p class="lead">' . e($t['nf_text'] ?? 'Səhifə tapılmadı') . '</p>'
   . '<p style="margin-top:24px"><a class="btn btn-primary" href="/">' . e($t['nav_home']) . '</a></p></div></section>';
require __DIR__ . '/includes/footer.php';
