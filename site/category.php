<?php
// Страница своей категории каталога: /bolme/{slug}/
// Категории заводятся в админке (data/categories.json), ключ приходит в $CAT_KEY.
require_once __DIR__ . '/includes/config.php';

$cat = categories()[$CAT_KEY ?? ''] ?? null;
if (!$cat) {
    http_response_code(404);
    $page = '404';
    $page_title = 'Vanilla Cake — 404';
    $page_meta  = $t['nf_text'];
    $page_robots = 'noindex, follow';
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-hero"><div class="container"><h1>404</h1><p class="lead">' . e($t['nf_text']) . '</p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page  = 'cat-' . $cat['key'];
$items = products_of($cat['key']);
$name  = cat_name($cat['key']);
$desc  = trim((string)($cat['desc_' . $lang] ?? $cat['desc'] ?? ''));

$page_title = seo_title('cat_' . $cat['key'], $name . ' - Vanilla.az');
$page_meta  = seo_desc('cat_' . $cat['key'], $desc !== '' ? $desc : $t['home_meta']);
if ($items) $og_image = CANON_HOST . asset($items[0]['img']);

$page_schema = 'CollectionPage';
schema_breadcrumbs([[$name, cat_url($cat)]]);
if ($items) schema_add(schema_item_list($items, $name));

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($name) ?></span>
    </div>
    <h1><?= e($name) ?></h1>
    <?php if ($desc !== ''): ?><p class="lead"><?= e($desc) ?></p><?php endif; ?>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <?php $catNavActive = 'cat-' . $cat['key']; require __DIR__ . '/includes/cat-nav.php'; ?>

    <?php if ($items): ?>
    <div class="pgrid">
      <?php foreach ($items as $i => $p) product_card($p, $i > 3); ?>
    </div>
    <?php else: ?>
    <p class="lead"><?= e($t['rev_empty']) ?></p>
    <?php endif; ?>

    <div class="sizes-wrap" style="margin-top:64px">
      <div class="size-card reveal">
        <h3><?= e($t['sizes_bento_h']) ?></h3>
        <p class="sub"><?= e($t['sizes_d']) ?></p>
        <div class="size-row"><span class="w"><?= e($t['size_b1_w']) ?></span><span class="p"><?= e($t['size_b1_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b1_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b2_w']) ?></span><span class="p"><?= e($t['size_b2_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b2_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b3_w']) ?></span><span class="p"><?= e($t['size_b3_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b3_c']) ?></span></div>
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
