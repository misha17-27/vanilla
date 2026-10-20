<?php
$page = 'reviews';
require_once __DIR__ . '/includes/config.php';
$page_title = seo_title('reviews', $t['rev_title']);
$page_meta  = seo_desc('reviews', $t['rev_meta']);
$page_schema = 'CollectionPage';
schema_breadcrumbs([[$t['nav_reviews'], '/reyler/']]);
require __DIR__ . '/includes/header.php';
$rev = json_decode((string)@file_get_contents(__DIR__ . '/data/reviews.json'), true) ?: ['items' => []];
$items = $rev['items'] ?? [];
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="<?= e(u('/')) ?>"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['rev_h']) ?></span>
    </div>
    <h1><?= e($t['rev_h']) ?></h1>
    <p class="lead"><?= e($t['rev_d']) ?></p>
    <?php if ($items): ?>
    <div class="rev-count"><b><?= count($items) ?></b> <?= e($t['rev_count']) ?></div>
    <?php endif; ?>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <div class="g-review reveal">
      <svg class="g-ico" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
      <div class="g-review-txt">
        <h2><?= e($t['g_rev_t']) ?></h2>
        <p><?= e($t['g_rev_d']) ?></p>
      </div>
      <a class="btn btn-primary" href="<?= e(REVIEW_URL) ?>" target="_blank" rel="noopener"><?= e($t['g_rev_btn']) ?></a>
    </div>
    <?php
    $quotes = array_values(array_filter($items, fn($x) => trim((string)($x['text'] ?? '')) !== ''));
    foreach ($quotes as $q) {
        schema_add([
            '@type'        => 'Review',
            'reviewBody'   => $q['text'],
            'author'       => ['@type' => 'Person', 'name' => trim((string)($q['author'] ?? '')) ?: 'Vanilla Cake müştərisi'],
            'itemReviewed' => ['@id' => CANON_HOST . '/#organization'],
        ]);
    }
    ?>
    <?php if ($quotes): ?>
    <h2 class="rev-quotes-h"><?= e($t['rev_quotes_h']) ?></h2>
    <div class="rev-quotes">
      <?php foreach ($quotes as $q): ?>
      <figure class="rev-q reveal">
        <blockquote><?= e($q['text']) ?></blockquote>
        <?php if (!empty($q['author'])): ?><figcaption>@<?= e($q['author']) ?></figcaption><?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($items): ?>
    <div class="rev-grid" id="rev-grid">
      <?php foreach ($items as $i => $it): ?>
      <button type="button" class="rev-item reveal" data-src="<?= e(asset($it['file'])) ?>">
        <img loading="lazy" decoding="async" src="<?= e(thumb_url($it['file'])) ?>" alt="<?= e($t['rev_h']) ?>"
             width="<?= (int)($it['w'] ?? 640) ?>" height="<?= (int)($it['h'] ?? 1138) ?>">
      </button>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="muted"><?= e($t['rev_empty']) ?></p>
    <?php endif; ?>

    <div class="more-card reveal">
      <div>
        <h3><?= e($t['rev_cta_t']) ?></h3>
        <p><?= e($t['rev_cta_d']) ?></p>
      </div>
      <a class="btn btn-primary" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
        <?= e($t['btn_wa']) ?>
      </a>
    </div>
  </div>
</section>

<div class="rev-box" id="rev-box" hidden>
  <button type="button" class="rev-x" id="rev-x" aria-label="Close">×</button>
  <button type="button" class="rev-nav prev" id="rev-prev" aria-label="Prev">‹</button>
  <img id="rev-img" src="" alt="">
  <button type="button" class="rev-nav next" id="rev-next" aria-label="Next">›</button>
</div>
<script>
(function () {
  var box = document.getElementById('rev-box');
  if (!box) return;
  var img = document.getElementById('rev-img');
  var items = [].slice.call(document.querySelectorAll('.rev-item'));
  var idx = 0;
  function show(i) {
    idx = (i + items.length) % items.length;
    img.src = items[idx].dataset.src;
    box.hidden = false;
    document.documentElement.classList.add('no-scroll');
  }
  function close() { box.hidden = true; document.documentElement.classList.remove('no-scroll'); }
  items.forEach(function (el, i) { el.addEventListener('click', function () { show(i); }); });
  document.getElementById('rev-x').addEventListener('click', close);
  document.getElementById('rev-prev').addEventListener('click', function () { show(idx - 1); });
  document.getElementById('rev-next').addEventListener('click', function () { show(idx + 1); });
  box.addEventListener('click', function (e) { if (e.target === box) close(); });
  document.addEventListener('keydown', function (e) {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(idx - 1);
    if (e.key === 'ArrowRight') show(idx + 1);
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
