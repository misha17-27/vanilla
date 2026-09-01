<?php
// ===== Language =====
session_start();
$LANGS = ['ru', 'az', 'en'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $LANGS, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ru';
$t = require __DIR__ . "/../lang/$lang.php";

// Правки текстов из админки (раздел «Страницы») перекрывают lang/*.php.
// Пустое значение = осталось как в переводе по умолчанию.
const TEXTS_FILE = __DIR__ . '/../data/texts.json';
$TEXTS = json_decode((string)@file_get_contents(TEXTS_FILE), true) ?: [];
foreach (($TEXTS[$lang] ?? []) as $k => $v) {
    if ($v === '' || $v === null || $v === []) continue;
    $t[$k] = $v;
}

// CSRF-токен для форм (загрузка дизайна и т.п.)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// ===== Contacts =====
// Значения по умолчанию; то, что задано через админку (data/settings.json), их перекрывает.
const SETTINGS_FILE = __DIR__ . '/../data/settings.json';
const SETTING_KEYS  = ['phone_display', 'phone_tel', 'wa_number', 'email', 'ig_url', 'ig_handle', 'fb_url', 'map_url', 'map_lat', 'map_lng', 'canon_host'];
$SETTINGS = (json_decode((string)@file_get_contents(SETTINGS_FILE), true) ?: []) + [
    'phone_display' => '+994 55 215 63 43',
    'phone_tel'     => '+994552156343',
    'wa_number'     => '994552156343',
    'email'         => 'info@vanilla.az',
    'ig_url'        => 'https://www.instagram.com/vanilla_cake_az/',
    'ig_handle'     => '@vanilla_cake_az',
    'fb_url'        => 'https://www.facebook.com/vanillacakeaz',
    'map_url'       => 'https://www.google.com/maps/place/Vanilla/@40.4196217,49.8047583,17z/data=!3m1!4b1!4m6!3m5!1s0x40308751a035afbd:0xb1a83ee0808b7a8d!8m2!3d40.4196217!4d49.8073332!16s%2Fg%2F11rnpg56rn',
    'map_lat'       => '40.4196217',
    'map_lng'       => '49.8073332',
    'canon_host'    => 'https://vanilla.az',
];
define('PHONE_DISPLAY', $SETTINGS['phone_display']);
define('PHONE_TEL',     $SETTINGS['phone_tel']);
define('WA_NUMBER',     $SETTINGS['wa_number']);
define('EMAIL',         $SETTINGS['email']);
define('IG_URL',        $SETTINGS['ig_url']);
define('IG_HANDLE',     $SETTINGS['ig_handle']);
define('FB_URL',        $SETTINGS['fb_url']);
define('MAP_URL',       $SETTINGS['map_url']);
define('MAP_LAT',       $SETTINGS['map_lat']);
define('MAP_LNG',       $SETTINGS['map_lng']);
define('CANON_HOST',    rtrim($SETTINGS['canon_host'], '/')); // production domain for canonical/sitemap

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Локальные пути к ассетам делаем корневыми (важно для страниц вида /mehsul/slug/)
function asset(string $p): string
{
    return preg_match('#^(https?:)?//#', $p) ? $p : '/' . ltrim($p, '/');
}

// WhatsApp link with prefilled message (optionally for a specific product).
// $url — ссылка на страницу товара: WhatsApp показывает по ней превью с фото (og:image).
function wa_link(?string $product = null, ?string $url = null): string
{
    global $t;
    $msg = $product ? sprintf($t['wa_msg_p'], $product) : $t['wa_msg'];
    if ($url !== null) {
        $msg .= "\n" . $t['wa_link_lbl'] . ': ' . $url;
    }
    return 'https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode($msg);
}

// Current path (без query), для language switcher и canonical
function current_path(): string
{
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $p;
}

function lang_url(string $l): string
{
    return current_path() . '?lang=' . $l;
}

// Канонический адрес: язык по умолчанию — на чистом URL, остальные — с ?lang=
function canonical_url(?string $l = null): string
{
    global $lang;
    $l = $l ?? $lang;
    return CANON_HOST . current_path() . ($l === 'ru' ? '' : '?lang=' . $l);
}

// ===== Разметка schema.org =====
// Страницы добавляют свои узлы через schema_add(); футер печатает их одним @graph.
$SCHEMA = [];

function schema_add(array $node): void
{
    global $SCHEMA;
    $SCHEMA[] = $node;
}

// Хлебные крошки: [[название, адрес], …] — «Главная» подставляется сама
function schema_breadcrumbs(array $items): void
{
    global $t;
    $list = [['@type' => 'ListItem', 'position' => 1, 'name' => $t['breadcrumb_home'], 'item' => CANON_HOST . '/']];
    foreach ($items as $i => [$name, $url]) {
        $node = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => $name];
        if ($url) $node['item'] = CANON_HOST . $url;
        $list[] = $node;
    }
    schema_add(['@type' => 'BreadcrumbList', '@id' => canonical_url() . '#breadcrumb', 'itemListElement' => $list]);
}

// Список товаров раздела для разметки CollectionPage
function schema_item_list(array $products, string $name): array
{
    $items = [];
    foreach ($products as $i => $p) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => CANON_HOST . product_url($p),
            'name'     => product_name($p),
            'image'    => CANON_HOST . asset($p['img']),
        ];
    }
    return [
        '@type'           => 'ItemList',
        '@id'             => canonical_url() . '#items',
        'name'            => $name,
        'numberOfItems'   => count($items),
        'itemListElement' => $items,
    ];
}

// Цены товара: из строки «35 – 60 ₼» получаем минимум и максимум
function price_range(string $price): array
{
    preg_match_all('/\d+(?:[.,]\d+)?/u', $price, $m);
    $nums = array_map(fn($n) => (float)str_replace(',', '.', $n), $m[0] ?: ['0']);
    return [min($nums), max($nums)];
}

// ===== SEO (снято с vanilla.az, чтобы сохранить позиции) =====
$SEO = json_decode((string)@file_get_contents(__DIR__ . '/../data/seo.json'), true) ?: [];

function seo_title(string $key, string $fallback): string
{
    global $SEO;
    return trim($SEO[$key]['title'] ?? '') !== '' ? $SEO[$key]['title'] : $fallback;
}

function seo_desc(string $key, string $fallback): string
{
    global $SEO;
    return trim($SEO[$key]['desc'] ?? '') !== '' ? $SEO[$key]['desc'] : $fallback;
}

// ===== Catalog =====
// Единый каталог в data/catalog.json (снят с vanilla.az + новые дизайны).
// Редактируется через админ-панель /admin/.
const CATALOG_FILE = __DIR__ . '/../data/catalog.json';
$CATALOG = json_decode((string)@file_get_contents(CATALOG_FILE), true) ?: ['products' => []];

$PRODUCTS = $CATALOG['products'];
foreach ($PRODUCTS as &$pp) {
    $pp['img'] = asset($pp['img']);
}
unset($pp);

$PRODUCTS_BY_SLUG = [];
foreach ($PRODUCTS as $pp) {
    $PRODUCTS_BY_SLUG[$pp['slug']] = $pp;
}

function products_of(string $type): array
{
    global $PRODUCTS;
    return array_values(array_filter($PRODUCTS, fn($p) => $p['type'] === $type));
}

// ===== Категории каталога =====
// Четыре базовые (bento / bantik / set / ctg) повторяют структуру vanilla.az —
// их можно переименовать, но не удалить. Новые добавляются через админку и
// выводятся отдельным блоком на своей странице раздела.
const CATEGORIES_FILE = __DIR__ . '/../data/categories.json';

function categories(): array
{
    static $cats = null;
    if ($cats === null) {
        $raw = json_decode((string)@file_get_contents(CATEGORIES_FILE), true);
        $cats = [];
        foreach ($raw['categories'] ?? [] as $c) {
            if (!empty($c['key'])) $cats[$c['key']] = $c;
        }
    }
    return $cats;
}

// Название категории на текущем языке
function cat_name(string $key): string
{
    global $lang;
    $c = categories()[$key] ?? null;
    if (!$c) return $key;
    $loc = trim((string)($c['name_' . $lang] ?? ''));
    return $loc !== '' ? $loc : (string)$c['name'];
}

// Свои категории (не базовые), закреплённые за страницей раздела
function extra_categories(string $page): array
{
    return array_values(array_filter(categories(), fn($c) => empty($c['builtin']) && ($c['page'] ?? '') === $page));
}

// Категории со своей страницей /bolme/{slug}/
function own_categories(): array
{
    return array_values(array_filter(categories(), fn($c) => ($c['page'] ?? '') === 'own'));
}

function cat_url(array $c): string
{
    // path — если у категории закреплён адрес со старого сайта
    return $c['path'] ?? ('/bolme/' . ($c['slug'] ?? $c['key']) . '/');
}

function product_url(array $p): string
{
    return '/mehsul/' . $p['slug'] . '/';
}

// Локализованное отображаемое имя из WP-названия товара
function product_name(array $p): string
{
    global $t, $lang;
    $title = $p['title'];
    switch ($p['type']) {
        case 'bantik':
            $rest = trim(preg_replace('/^Bento tort bantik\s*/iu', '', $title));
            return $t['p_bantik'] . ($rest !== '' ? ' «' . $rest . '»' : '');
        case 'set':
            $rest = trim(preg_replace('/^(Bento tort|Bento set)\s*/iu', '', $title));
            return $t['p_set'] . ($rest !== '' ? ' «' . $rest . '»' : '');
        case 'ctg':
            $rest = trim(preg_replace('/^Cake to go\s*[–—-]?\s*/iu', '', $title));
            return 'Cake to go' . ($rest !== '' ? ' — ' . $rest : '');
        default:
            // своя категория — берём её название в единственном числе
            $c = categories()[$p['type']] ?? null;
            if ($c && empty($c['builtin'])) {
                $label = trim((string)($c['name_one_' . $lang] ?? $c['name_one'] ?? '')) ?: cat_name($p['type']);
                $strip = (string)($c['strip'] ?? 'Bento tort');
                $rest  = trim(preg_replace('/^' . preg_quote($strip, '/') . '\s*/iu', '', $title));
                return $label . ($rest !== '' ? ' «' . $rest . '»' : '');
            }
            $rest = trim(preg_replace('/^Bento tort\s*/iu', '', $title));
            return $t['p_bento'] . ($rest !== '' ? ' «' . $rest . '»' : '');
    }
}

// ===== Fillings photos (порядок = fl{i}_items в lang-файлах) =====
$FILLING_IMGS = [
    1 => ['fill-v-banan', 'fill-v-ciyelek', 'fill-v-visne', 'fill-v-malina', 'fill-v-karamel', 'fill-v-karamel-banan', 'fill-v-krem'],
    2 => ['fill-s-ciyelek', 'fill-s-malina', 'fill-s-visne', 'fill-s-banan', 'fill-s-karamel', 'fill-s-snikers', 'fill-s-krem'],
    3 => ['fill-r-ciyelek', 'fill-r-visne', 'fill-r-malina'],
];

function filling_img(int $sponge, int $idx): string
{
    global $FILLING_IMGS;
    $f = $FILLING_IMGS[$sponge][$idx] ?? '';
    return $f !== '' ? '/assets/img/fillings/' . $f . '.jpg' : '';
}

// Photo rows list for one sponge
function filling_rows(int $sponge): void
{
    global $t;
    echo '<div class="fill-rows">';
    foreach ($t["fl{$sponge}_items"] as $idx => $label) {
        $img = filling_img($sponge, $idx);
        echo '<span class="fr">';
        if ($img !== '') {
            echo '<img loading="lazy" src="' . e($img) . '" alt="' . e($label) . '" width="480" height="480">';
        }
        echo '<i>' . e($label) . '</i></span>';
    }
    echo '</div>';
}

// Render one product card
function product_card(array $p, bool $lazy = true, ?string $cat = null): void
{
    $name = product_name($p);
    $url  = product_url($p);
    $wa   = wa_link($name, CANON_HOST . $url);
    $loading = $lazy ? 'loading="lazy"' : 'fetchpriority="high"';
    echo '<article class="pcard reveal"' . ($cat !== null ? ' data-cat="' . e($cat) . '"' : '') . '>
      <a class="pcard-ph" href="' . e($url) . '" aria-label="' . e($name) . '">
        <img ' . $loading . ' src="' . e($p['img']) . '" alt="' . e($name) . '" width="600" height="600">
      </a>
      <div class="pcard-body">
        <h3><a href="' . e($url) . '">' . e($name) . '</a></h3>
        <div class="pcard-row">
          <span class="price">' . e($p['price']) . '</span>
          <a class="pcard-wa" href="' . e($wa) . '" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
          </a>
        </div>
      </div>
    </article>';
}
