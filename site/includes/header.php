<?php
// Expects: $page (slug for nav highlight), $page_title, $page_meta; optional $og_image
require_once __DIR__ . '/config.php';
$nav = [
    'index'    => ['/',                    $t['nav_home']],
    'bento'    => ['/bolme/bento-tort/',   $t['nav_bento']],
    'ctg'      => ['/bolme/cake-to-go/',   $t['nav_ctg']],
    'fillings' => ['/terkibler/',          $t['nav_fillings']],
    'konstr'   => ['/konstruktor/',        $t['nav_konstr']],
    'about'    => ['/haqqimizda/',         $t['nav_about']],
    'faq'      => ['/faq/',                $t['nav_faq']],
    'contact'  => ['/elaqe/',              $t['nav_contact']],
];
$canonical = CANON_HOST . current_path();
$og_image  = $og_image ?? (CANON_HOST . '/assets/logo.svg');
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_meta) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_meta) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($og_image) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍰</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://vanilla.az">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
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
        <?php foreach ($nav as $slug => [$href, $label]): ?>
        <li><a href="<?= $href ?>" class="<?= $slug === ($page ?? '') ? 'active' : '' ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="nav-right">
      <div class="lang" role="group" aria-label="Language">
        <?php foreach ($LANGS as $l): ?>
        <a href="<?= e(lang_url($l)) ?>" class="<?= $l === $lang ? 'active' : '' ?>"><?= strtoupper($l) ?></a>
        <?php endforeach; ?>
      </div>
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
