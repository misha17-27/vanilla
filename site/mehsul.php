<?php
require __DIR__ . '/includes/config.php';

$slug = $ROUTE_SLUG ?? '';
$prod = $PRODUCTS_BY_SLUG[$slug] ?? null;
if (!$prod) {
    http_response_code(404);
    $page = '404';
    $page_title = 'Vanilla Cake — 404';
    $page_meta  = '';
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-hero"><div class="container"><h1>404</h1><p class="lead">' . e($t['nf_text']) . '</p>'
       . '<p style="margin-top:24px"><a class="btn btn-primary" href="/bolme/bento-tort/">' . e($t['nav_bento']) . '</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$name = product_name($prod);
// SEO — как на vanilla.az; для новых дизайнов генерируем в том же стиле
$page_title = trim($prod['seo_title'] ?? '') !== '' ? $prod['seo_title'] : $prod['title'] . ' - Vanilla.az';
$page_meta  = trim($prod['seo_desc'] ?? '') !== '' ? $prod['seo_desc'] : $t['home_meta'];
$og_image   = $prod['img'];

// категория для крошек и раздела «похожие»
$catMap = [
    'bento'  => ['/bolme/bento-tort/',        $t['nav_bento'],  'bento'],
    'bantik' => ['/bolme/bento-tort/#bantik', $t['bantik_h'],   'bento'],
    'set'    => ['/bolme/bento-tort/#sets',   $t['sets_h'],     'bento'],
    'ctg'    => ['/bolme/cake-to-go/',        $t['nav_ctg'],    'ctg'],
];
[$catUrl, $catLabel, $navSlug] = $catMap[$prod['type']];
$page = $navSlug;

$weightKey = 'pd_w_' . $prod['type'];

// похожие: тот же тип, без текущего
$related = array_values(array_filter(products_of($prod['type']), fn($p) => $p['slug'] !== $slug));
shuffle($related);
$related = array_slice($related, 0, 4);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero prod-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <a href="<?= e($catUrl) ?>"><?= e($catLabel) ?></a>
      <span class="sep">/</span>
      <span><?= e($name) ?></span>
    </div>
  </div>
</section>

<section class="prod-main">
  <div class="container">
    <div class="prod-layout">
      <div class="prod-ph reveal in">
        <img src="<?= e($prod['img']) ?>" alt="<?= e($name) ?>" width="600" height="600" fetchpriority="high">
      </div>
      <div class="prod-info">
        <h1><?= e($name) ?></h1>
        <div class="prod-price"><?= e($prod['price']) ?></div>
        <p class="prod-weight"><?= e($t[$weightKey]) ?></p>
        <div class="prod-ctas">
          <a class="btn btn-primary" href="<?= e(wa_link($name)) ?>" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
            <?= e($t['pd_order']) ?>
          </a>
        </div>
        <div class="prod-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="18" height="17" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
          <span><?= e($t['pd_note']) ?></span>
        </div>
        <ul class="prod-perks">
          <li><?= e($t['u1t']) ?></li>
          <li><?= e($t['u2t']) ?></li>
          <li><?= e($t['u4d']) ?></li>
        </ul>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-wrap reveal">
      <div class="tabs-nav" role="tablist">
        <button class="tab-btn active" data-tab="desc"><?= e($t['tab_desc']) ?></button>
        <button class="tab-btn" data-tab="fill"><?= e($t['tab_fill']) ?></button>
        <button class="tab-btn" data-tab="time"><?= e($t['tab_time']) ?></button>
        <button class="tab-btn" data-tab="del"><?= e($t['tab_del']) ?></button>
      </div>
      <div class="tab-panel active" data-panel="desc">
        <p><?= e($t['pd_desc']) ?></p>
        <p class="tp-strong"><?= e($t[$weightKey]) ?></p>
      </div>
      <div class="tab-panel" data-panel="fill">
        <div class="tp-fill-grid">
          <?php foreach ([1, 2, 3] as $i): ?>
          <div class="tp-fill">
            <b><?= e($t["fl{$i}_t"]) ?> <?= e($t["fl{$i}_s"]) ?></b>
            <span><?= e(implode(' · ', $t["fl{$i}_items"])) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="margin-top:18px"><?= e($t['fl_d']) ?> <a class="tp-link" href="/terkibler/"><?= e($t['nav_fillings']) ?> →</a></p>
      </div>
      <div class="tab-panel" data-panel="time">
        <p><?= e($t['pd_time']) ?></p>
      </div>
      <div class="tab-panel" data-panel="del">
        <p><?= e($t['pd_del']) ?></p>
      </div>
    </div>

    <?php if ($related): ?>
    <div class="sec-head" style="margin-top:76px">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e($t['related_h']) ?></h2>
    </div>
    <div class="pgrid">
      <?php foreach ($related as $p) product_card($p); ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
