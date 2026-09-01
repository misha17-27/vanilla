<?php
$page = 'bento';
require __DIR__ . '/includes/config.php';
$page_title = seo_title('bento', $t['bento_title']);
$page_meta  = seo_desc('bento', $t['bento_meta']);
require __DIR__ . '/includes/header.php';
$bento  = products_of('bento');
$bantik = products_of('bantik');
$sets   = products_of('set');
$page_schema = 'CollectionPage';
schema_breadcrumbs([[$t['nav_bento'], '/bolme/bento-tort/']]);
schema_add(schema_item_list(array_merge($bento, $bantik, $sets), $t['bento_h']));
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_bento']) ?></span>
    </div>
    <h1><?= e($t['bento_h']) ?></h1>
    <p class="lead"><?= e($t['bento_d']) ?></p>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <?php $catNavActive = 'bento'; require __DIR__ . '/includes/cat-nav.php'; ?>

    <div class="pgrid">
      <?php foreach ($bento as $i => $p) product_card($p, $i > 3); ?>
    </div>

    <div class="sec-head" id="bantik" style="margin-top:84px">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e($t['bantik_h']) ?></h2>
      <p><?= e($t['bantik_d']) ?></p>
    </div>
    <div class="pgrid">
      <?php foreach ($bantik as $p) product_card($p); ?>
    </div>

    <div class="sec-head" id="sets" style="margin-top:84px">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e($t['sets_h']) ?></h2>
      <p><?= e($t['sets_d']) ?></p>
    </div>
    <div class="pgrid">
      <?php foreach ($sets as $p) product_card($p); ?>
    </div>

    <?php foreach (own_categories() as $c): $items = products_of($c['key']); if (!$items) continue; ?>
    <div class="head-row" id="cat-<?= e($c['key']) ?>" style="margin-top:84px">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e(cat_name($c['key'])) ?></h2>
        <?php $cDesc = trim((string)($c['desc_' . $lang] ?? $c['desc'] ?? '')); ?>
        <?php if ($cDesc !== ''): ?><p><?= e($cDesc) ?></p><?php endif; ?>
      </div>
      <a class="btn btn-ghost" href="<?= e(cat_url($c)) ?>"><?= e($t['btn_all_bento']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($items, 0, 8) as $p) product_card($p); ?>
    </div>
    <?php endforeach; ?>

    <?php foreach (extra_categories('bento') as $c): $items = products_of($c['key']); if (!$items) continue; ?>
    <div class="sec-head" id="cat-<?= e($c['key']) ?>" style="margin-top:84px">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e(cat_name($c['key'])) ?></h2>
      <?php if (trim((string)($c['desc'] ?? '')) !== ''): ?><p><?= e($c['desc']) ?></p><?php endif; ?>
    </div>
    <div class="pgrid">
      <?php foreach ($items as $p) product_card($p); ?>
    </div>
    <?php endforeach; ?>

    <div class="sizes-wrap" style="margin-top:64px">
      <div class="size-card reveal">
        <h3><?= e($t['sizes_bento_h']) ?></h3>
        <p class="sub"><?= e($t['sizes_d']) ?></p>
        <div class="size-row"><span class="w"><?= e($t['size_b1_w']) ?></span><span class="p"><?= e($t['size_b1_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b1_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b2_w']) ?></span><span class="p"><?= e($t['size_b2_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b2_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b3_w']) ?></span><span class="p"><?= e($t['size_b3_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b3_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_bk_w']) ?></span><span class="p"><?= e($t['size_bk_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_bk_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_st_w']) ?></span><span class="p"><?= e($t['size_st_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_st_c']) ?></span></div>
      </div>
      <div class="size-card reveal">
        <h3><?= e($t['fl_t']) ?></h3>
        <p class="sub"><?= e($t['fl_d']) ?></p>
        <div class="size-row"><span class="w"><?= e($t['fl1_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl1_s']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['fl2_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl2_s']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['fl3_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl3_s']) ?></span></div>
        <div class="size-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span><?= e($t['fl_d']) ?> <a href="/terkibler/" style="color:var(--pink);font-weight:600"><?= e($t['nav_fillings']) ?> →</a></span>
        </div>
      </div>
    </div>

    <div class="more-card reveal">
      <div>
        <h3><?= e($t['bento_more_t']) ?></h3>
        <p><?= e($t['bento_more_d']) ?></p>
      </div>
      <a class="btn btn-primary" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg>
        Instagram
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
