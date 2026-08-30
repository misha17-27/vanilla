<?php
// ===== Language =====
session_start();
$LANGS = ['ru', 'az', 'en'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $LANGS, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ru';
$t = require __DIR__ . "/../lang/$lang.php";

// CSRF-токен для форм (загрузка дизайна и т.п.)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// ===== Contacts =====
const PHONE_DISPLAY = '+994 55 215 63 43';
const PHONE_TEL     = '+994552156343';
const WA_NUMBER     = '994552156343';
const EMAIL         = 'info@vanilla.az';
const IG_URL        = 'https://www.instagram.com/vanilla_cake_az/';
const IG_HANDLE     = '@vanilla_cake_az';
const FB_URL        = 'https://www.facebook.com/vanillacakeaz';
const MAP_URL       = 'https://www.google.com/maps/place/Vanilla/@40.4196217,49.8047583,17z/data=!3m1!4b1!4m6!3m5!1s0x40308751a035afbd:0xb1a83ee0808b7a8d!8m2!3d40.4196217!4d49.8073332!16s%2Fg%2F11rnpg56rn';
const MAP_LAT       = '40.4196217';
const MAP_LNG       = '49.8073332';
const CANON_HOST    = 'https://vanilla.az'; // production domain for canonical/sitemap

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
// Основной каталог снят с vanilla.az (slug, названия, цены, SEO — как в WooCommerce).
// Дополнительно — новые дизайны с локальными фото (новые URL, на позиции не влияют).
$CATALOG = json_decode((string)@file_get_contents(__DIR__ . '/../data/catalog.json'), true) ?: ['products' => []];

$EXTRAS = [
    ['slug' => 'bento-tort-bantik-noir',  'title' => 'Bento tort bantik Noir',  'type' => 'bantik', 'price' => '35 – 60 ₼', 'img' => 'assets/img/bantik-noir.jpg'],
    ['slug' => 'bento-tort-bantik-blush', 'title' => 'Bento tort bantik Blush', 'type' => 'bantik', 'price' => '35 – 60 ₼', 'img' => 'assets/img/bantik-blush.jpg'],
    ['slug' => 'bento-tort-bantik-rose',  'title' => 'Bento tort bantik Rose',  'type' => 'bantik', 'price' => '35 – 60 ₼', 'img' => 'assets/img/bantik-rose.jpg'],
    ['slug' => 'bento-tort-bantik-ivory', 'title' => 'Bento tort bantik Ivory', 'type' => 'bantik', 'price' => '35 – 60 ₼', 'img' => 'assets/img/bantik-ivory.jpg'],
    ['slug' => 'bento-set-pinky',   'title' => 'Bento set Pinky',   'type' => 'set', 'price' => '100 ₼', 'img' => 'assets/img/set-pinky.jpg'],
    ['slug' => 'bento-set-classic', 'title' => 'Bento set Classic', 'type' => 'set', 'price' => '100 ₼', 'img' => 'assets/img/set-white.jpg'],
    ['slug' => 'bento-set-kids',    'title' => 'Bento set Kids',    'type' => 'set', 'price' => '100 ₼', 'img' => 'assets/img/set-kids.jpg'],
    ['slug' => 'bento-set-sky',     'title' => 'Bento set Sky',     'type' => 'set', 'price' => '75 ₼',  'img' => 'assets/img/set-blue.jpg'],
    ['slug' => 'cake-to-go-minimal',  'title' => 'Cake to go Minimal',  'type' => 'ctg', 'price' => '25 – 30 ₼', 'img' => 'assets/img/ctg-minimal.jpg'],
    ['slug' => 'cake-to-go-princess', 'title' => 'Cake to go Princess', 'type' => 'ctg', 'price' => '25 – 30 ₼', 'img' => 'assets/img/ctg-princess.jpg'],
    ['slug' => 'cake-to-go-daisy',    'title' => 'Cake to go Daisy',    'type' => 'ctg', 'price' => '25 – 30 ₼', 'img' => 'assets/img/ctg-daisy.jpg'],
    ['slug' => 'cake-to-go-blue',     'title' => 'Cake to go Blue',     'type' => 'ctg', 'price' => '25 – 30 ₼', 'img' => 'assets/img/ctg-blue.jpg'],
    ['slug' => 'cake-to-go-new-year', 'title' => 'Cake to go New Year', 'type' => 'ctg', 'price' => '25 – 30 ₼', 'img' => 'assets/img/ctg-newyear.jpg'],
];

// Локальные фото для товаров, у которых есть страница на старом сайте
$IMG_OVERRIDES = [
    'cake-to-go-love'              => 'assets/img/ctg-love.jpg',
    'cake-to-go-school-2'          => 'assets/img/ctg-school.jpg',
    'cake-to-go-happi-birthday-6x' => 'assets/img/ctg-birthday.jpg',
];

$PRODUCTS = array_merge($EXTRAS, $CATALOG['products']);
foreach ($PRODUCTS as &$pp) {
    if (isset($IMG_OVERRIDES[$pp['slug']])) {
        $pp['img'] = $IMG_OVERRIDES[$pp['slug']];
    }
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

function product_url(array $p): string
{
    return '/mehsul/' . $p['slug'] . '/';
}

// Локализованное отображаемое имя из WP-названия товара
function product_name(array $p): string
{
    global $t;
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
function product_card(array $p, bool $lazy = true): void
{
    $name = product_name($p);
    $url  = product_url($p);
    $wa   = wa_link($name, CANON_HOST . $url);
    $loading = $lazy ? 'loading="lazy"' : 'fetchpriority="high"';
    echo '<article class="pcard reveal">
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
