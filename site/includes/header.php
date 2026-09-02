<?php
// Expects: $page (slug for nav highlight), $page_title, $page_meta; optional $og_image
require_once __DIR__ . '/config.php';
// Подменю «Бенто-торты»: свои категории со своей страницей + бантики и сеты,
// у каждой — фото первого торта категории
$firstImg = function (string $type): string {
    $items = products_of($type);
    return $items ? asset($items[0]['img']) : '';
};
$bentoSub = [];
foreach (own_categories() as $c) {
    $bentoSub['cat-' . $c['key']] = [cat_url($c), cat_name($c['key']), $firstImg($c['key'])];
}

$nav = [
    'index'    => ['/',                    $t['nav_home']],
    'bento'    => ['/bolme/bento-tort/',   $t['nav_bento'], $bentoSub],
    'ctg'      => ['/bolme/cake-to-go/',   $t['nav_ctg']],
    'fillings' => ['/terkibler/',          $t['nav_fillings']],
    'konstr'   => ['/konstruktor/',        $t['nav_konstr']],
    'contact'  => ['/elaqe/',              $t['nav_contact']],
];
// «Отзывы», «О нас» и FAQ в верхнем меню не показываем — они в подвале
$canonical = canonical_url();
$og_image  = $og_image ?? (CANON_HOST . '/assets/og-cover.jpg');
$og_type   = $og_type ?? 'website';
$OG_LOCALE = ['ru' => 'ru_RU', 'az' => 'az_AZ', 'en' => 'en_US'];
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_meta) ?>">
<meta name="robots" content="<?= e($page_robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1') ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach ($LANGS as $l): ?>
<link rel="alternate" hreflang="<?= e($l) ?>" href="<?= e(canonical_url($l)) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= e(canonical_url('ru')) ?>">
<meta property="og:type" content="<?= e($og_type) ?>">
<meta property="og:site_name" content="Vanilla Cake">
<meta property="og:locale" content="<?= e($OG_LOCALE[$lang]) ?>">
<?php foreach ($LANGS as $l): if ($l === $lang) continue; ?>
<meta property="og:locale:alternate" content="<?= e($OG_LOCALE[$l]) ?>">
<?php endforeach; ?>
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_meta) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($og_image) ?>">
<meta property="og:image:alt" content="<?= e($page_title) ?>">
<?php if (!empty($og_price)): ?>
<meta property="product:price:amount" content="<?= e((string)$og_price) ?>">
<meta property="product:price:currency" content="AZN">
<meta property="product:availability" content="preorder">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page_title) ?>">
<meta name="twitter:description" content="<?= e($page_meta) ?>">
<meta name="twitter:image" content="<?= e($og_image) ?>">
<meta name="theme-color" content="#ffffff">
<meta name="geo.region" content="AZ-BA">
<meta name="geo.placename" content="Bakı">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="icon" href="/assets/favicon-192.png" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://vanilla.az">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css?v=<?= @filemtime(__DIR__ . '/../assets/style.css') ?>">
</head>
<body>

<div class="topbar">
  <div class="container">
    <span><?= e($t['preorder_note']) ?> · <?= e($t['hours']) ?></span>
    <a class="hide-m" href="tel:<?= PHONE_TEL ?>"><?= PHONE_DISPLAY ?></a>
  </div>
</div>

<header class="site-header">
  <div class="container nav">
    <a class="logo" href="/">
      <img src="/assets/logo.svg" alt="Vanilla Cake" width="180" height="40">
    </a>
    <nav>
      <ul class="menu" id="menu">
        <?php foreach ($nav as $slug => $item): ?>
        <?php [$href, $label] = $item; $sub = $item[2] ?? null; ?>
        <li class="<?= $sub ? 'has-sub' : '' ?>">
          <a href="<?= $href ?>" class="<?= $slug === ($page ?? '') || ($sub && isset($sub[$page ?? ''])) ? 'active' : '' ?>">
            <?= e($label) ?>
            <?php if ($sub): ?><svg class="sub-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg><?php endif; ?>
          </a>
          <?php if ($sub): ?>
          <div class="submenu">
            <div class="submenu-in">
              <?php foreach ($sub as $sSlug => [$sHref, $sLabel, $sImg]): ?>
              <a class="sub-item <?= $sSlug === ($page ?? '') ? 'active' : '' ?>" href="<?= e($sHref) ?>">
                <?php if ($sImg): ?><img loading="lazy" src="<?= e($sImg) ?>" alt="" width="56" height="56"><?php endif; ?>
                <span><?= e($sLabel) ?></span>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li class="menu-foot">
          <a class="btn btn-wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
            <?= e($t['btn_wa']) ?>
          </a>
          <div class="menu-soc">
            <a href="<?= IG_URL ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg></a>
            <a href="<?= FB_URL ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 8.5V7a1.5 1.5 0 0 1 1.5-1.5H17V2h-3a4 4 0 0 0-4 4v2.5H7.5V12H10v10h4V12h2.6l.9-3.5H14Z"/></svg></a>
            <a href="tel:<?= PHONE_TEL ?>" aria-label="<?= e(PHONE_DISPLAY) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6.5 3h3l1.5 4-2 1.5a12 12 0 0 0 5.5 5.5L16 12l4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4 6.2 2 2 0 0 1 6.5 3Z"/></svg></a>
          </div>
          <span class="menu-tag"><?= e(PHONE_DISPLAY) ?> · <?= e($t['hours']) ?></span>
        </li>
      </ul>
    </nav>
    <div class="nav-right">
      <details class="lang" id="lang">
        <summary aria-label="Language">
          <span><?= strtoupper($lang) ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </summary>
        <div class="lang-menu">
          <?php foreach ($LANGS as $l): ?>
          <a href="<?= e(lang_url($l)) ?>" class="<?= $l === $lang ? 'active' : '' ?>" hreflang="<?= e($l) ?>"><?= strtoupper($l) ?></a>
          <?php endforeach; ?>
        </div>
      </details>
      <a class="btn btn-primary btn-nav" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
        <?= e($t['btn_wa_short']) ?>
      </a>
      <button class="burger" id="burger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
