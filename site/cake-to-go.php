<?php
$page = 'ctg';
require __DIR__ . '/includes/config.php';
$page_title = seo_title('ctg', $t['ctg_title']);
$page_meta  = seo_desc('ctg', $t['ctg_meta']);
require __DIR__ . '/includes/header.php';
$ctg = products_of('ctg');
$page_schema = 'CollectionPage';
schema_breadcrumbs([[$t['nav_ctg'], '/bolme/cake-to-go/']]);
schema_add(schema_item_list($ctg, $t['ctg_h']));
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_ctg']) ?></span>
    </div>
    <h1><?= e($t['ctg_h']) ?></h1>
    <p class="lead"><?= e($t['ctg_d']) ?></p>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <div class="pgrid">
      <?php foreach ($ctg as $i => $p) product_card($p, $i > 3); ?>
    </div>

    <?php foreach (extra_categories('ctg') as $c): $items = products_of($c['key']); if (!$items) continue; ?>
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
        <h3><?= e($t['ctg_sizes_h']) ?></h3>
        <div class="size-row"><span class="w"><?= e($t['size_c1_w']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_c1_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_c2_w']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_c2_c']) ?></span></div>
        <div class="size-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span><?= e($t['sizes_ctg_note']) ?></span>
        </div>
      </div>
      <div class="size-card reveal">
        <h3><?= e($t['fl_t']) ?></h3>
        <p class="sub"><?= e($t['fl_d']) ?></p>
        <div class="size-row"><span class="w"><?= e($t['fl1_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl1_s']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['fl2_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl2_s']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['fl3_t']) ?></span><span class="dots"></span><span class="c" style="color:var(--navy)"><?= e($t['fl3_s']) ?></span></div>
        <div class="size-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span><a href="/terkibler/" style="color:var(--pink);font-weight:600"><?= e($t['nav_fillings']) ?> →</a></span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
