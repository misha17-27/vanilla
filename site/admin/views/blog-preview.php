<?php
// Предпросмотр статьи в оформлении сайта — и для черновиков, которых на сайте нет.
// Показывает сохранённую версию; открывается только после входа в админку.
$pv = blog_all()[(string)($_GET['preview'] ?? '')] ?? null;
if (!$pv || !can('blog')) go('/admin/blog');
$l = (string)($_GET['l'] ?? 'ru');
if (!in_array($l, $LANGS, true)) $l = 'ru';

// тексты и ссылки — на языке предпросмотра
$lang = $l;
$LANG_BASE = $l === 'ru' ? '' : '/' . $l;
$t = require __DIR__ . '/../../lang/' . $l . '.php';
$body = blog_render((array)($pv[$l]['body'] ?? []));
$toc  = blog_toc((array)($pv[$l]['body'] ?? []));
$h1   = (string)($pv[$l]['title'] ?? '');
?><!DOCTYPE html>
<html lang="<?= e($l) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Предпросмотр: <?= e($h1 ?: $pv['slug']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css?v=<?= @filemtime(__DIR__ . '/../../assets/style.css') ?>">
<style>
  .pv-bar{position:sticky;top:0;z-index:50;display:flex;gap:14px;align-items:center;flex-wrap:wrap;padding:10px 24px;background:#16527F;color:#fff;font:600 13px Montserrat,sans-serif}
  .pv-bar a{color:#fff;text-decoration:underline}
  .pv-bar .on{background:#fff;color:#16527F;border-radius:99px;padding:3px 10px;text-decoration:none}
  .reveal{opacity:1!important;transform:none!important}
</style>
</head>
<body>
<div class="pv-bar">
  <span>Предпросмотр сохранённой версии<?= blog_state($pv) !== 'live' ? ' · на сайте статьи пока нет' : '' ?></span>
  <?php foreach ($LANGS as $x): ?>
  <a class="<?= $x === $l ? 'on' : '' ?>" href="/admin/blog?preview=<?= e($pv['slug']) ?>&amp;l=<?= $x ?>"><?= strtoupper($x) ?></a>
  <?php endforeach; ?>
  <a href="/admin/blog?edit=<?= e($pv['slug']) ?>">← к редактированию</a>
</div>
<section class="page-hero b-hero">
  <div class="container">
    <h1><?= e($h1 ?: '(нет заголовка на ' . strtoupper($l) . ')') ?></h1>
    <p class="b-meta"><?= e(blog_date((string)$pv['published'])) ?> · <?= e(sprintf($t['blog_min'], blog_minutes($pv))) ?></p>
  </div>
</section>
<section class="section b-section">
  <div class="container">
    <article class="b-article">
      <figure class="b-cover"><img src="<?= e(asset((string)$pv['image'])) ?>" alt="<?= e((string)($pv[$l]['image_alt'] ?? '')) ?>"></figure>
      <?php if (count($toc) >= 3): ?>
      <nav class="b-toc"><b><?= e($t['blog_toc']) ?></b><ol>
        <?php foreach ($toc as [$id, $label]): ?><li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li><?php endforeach; ?>
      </ol></nav>
      <?php endif; ?>
      <div class="b-body"><?= $body ?></div>
    </article>
  </div>
</section>
</body>
</html>
