<?php
// ===== Language =====
session_start();
$LANGS = ['ru', 'az', 'en'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $LANGS, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ru';
$t = require __DIR__ . "/../lang/$lang.php";

// ===== Contacts =====
const PHONE_DISPLAY = '+994 55 215 63 43';
const PHONE_TEL     = '+994552156343';
const WA_NUMBER     = '994552156343';
const EMAIL         = 'info@vanilla.az';
const IG_URL        = 'https://www.instagram.com/vanilla_cake_az/';
const IG_HANDLE     = '@vanilla_cake_az';
const FB_URL        = 'https://www.facebook.com/vanillacakeaz';

// WhatsApp link with prefilled message (optionally for a specific product)
function wa_link(?string $product = null): string
{
    global $t;
    $msg = $product ? sprintf($t['wa_msg_p'], $product) : $t['wa_msg'];
    return 'https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode($msg);
}

// Language switcher URL for current page
function lang_url(string $l): string
{
    return basename($_SERVER['PHP_SELF']) . '?lang=' . $l;
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ===== Products =====
// type: bento | bantik | set | ctg; base: design name; count: pieces (sets only)
$PRODUCTS = [
    ['type' => 'bento',  'base' => 'Sevgilim',            'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanilla-1-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Bear',                'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanilla_cake_az_248100673_609196293748610_903340673830523120_n-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Sweet',               'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanilla444-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Love you',            'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanilla6-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Baby',                'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1722410107_highlight18253086958008641-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Thank you with love', 'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanilla232323-600x600.jpg'],
    ['type' => 'bento',  'base' => 'Goat',                'price' => '25 – 60 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2021/11/vanillajklo-600x600.jpg'],
    ['type' => 'bantik', 'base' => 'Noir',                'price' => '35 – 60 ₼',  'img' => 'assets/img/bantik-noir.jpg'],
    ['type' => 'bantik', 'base' => 'Blush',               'price' => '35 – 60 ₼',  'img' => 'assets/img/bantik-blush.jpg'],
    ['type' => 'bantik', 'base' => 'Rose',                'price' => '35 – 60 ₼',  'img' => 'assets/img/bantik-rose.jpg'],
    ['type' => 'bantik', 'base' => 'Ivory',               'price' => '35 – 60 ₼',  'img' => 'assets/img/bantik-ivory.jpg'],
    ['type' => 'set',    'base' => 'Pinky',               'price' => '100 ₼', 'count' => 4, 'img' => 'assets/img/set-pinky.jpg'],
    ['type' => 'set',    'base' => 'Classic',             'price' => '100 ₼', 'count' => 4, 'img' => 'assets/img/set-white.jpg'],
    ['type' => 'set',    'base' => 'Kids',                'price' => '100 ₼', 'count' => 4, 'img' => 'assets/img/set-kids.jpg'],
    ['type' => 'set',    'base' => 'Sky',                 'price' => '75 ₼',  'count' => 3, 'img' => 'assets/img/set-blue.jpg'],
    ['type' => 'ctg',    'base' => 'Love',                'price' => '25 – 30 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1708977404_3311464938185466334_3523099162-600x600.jpg'],
    ['type' => 'ctg',    'base' => 'Happy birthday',      'price' => '25 – 30 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1712262810_3339024920686659731_3523099162-3-600x600.jpg'],
    ['type' => 'ctg',    'base' => 'School',              'price' => '25 – 30 ₼',  'img' => 'https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1663699781_2931648705222181408_3523099162-600x600.jpg'],
];

function product_name(array $p): string
{
    global $t;
    switch ($p['type']) {
        case 'bento':  return $t['p_bento'] . ' «' . $p['base'] . '»';
        case 'bantik': return $t['p_bantik'] . ' «' . $p['base'] . '»';
        case 'set':    return $t['p_set'] . ' «' . $p['base'] . '» · ' . $p['count'] . ' ' . $t['pcs'];
        default:       return 'Cake to go — ' . $p['base'];
    }
}

// Render one product card
function product_card(array $p, bool $lazy = true): void
{
    $name = product_name($p);
    $loading = $lazy ? 'loading="lazy"' : 'fetchpriority="high"';
    echo '<article class="pcard reveal">
      <a class="pcard-ph" href="' . e(wa_link($name)) . '" target="_blank" rel="noopener" aria-label="' . e($name) . '">
        <img ' . $loading . ' src="' . e($p['img']) . '" alt="' . e($name) . '" width="600" height="600">
      </a>
      <div class="pcard-body">
        <h3>' . e($name) . '</h3>
        <div class="pcard-row">
          <span class="price">' . e($p['price']) . '</span>
          <a class="pcard-wa" href="' . e(wa_link($name)) . '" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
          </a>
        </div>
      </div>
    </article>';
}
