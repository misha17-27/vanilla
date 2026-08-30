<?php
$page = 'fillings';
require __DIR__ . '/includes/config.php';
$page_title = $t['fil_title'];
$page_meta  = $t['fil_meta'];
require __DIR__ . '/includes/header.php';
$cards = [
    ['key' => 1, 'cls' => 'vanilla'],
    ['key' => 2, 'cls' => 'choco'],
    ['key' => 3, 'cls' => 'velvet'],
];
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_fillings']) ?></span>
    </div>
    <h1><?= e($t['fil_h']) ?></h1>
    <p class="lead"><?= e($t['fil_d']) ?></p>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <div class="fil-grid">
      <?php foreach ($cards as $c): $k = $c['key']; ?>
      <article class="fil-card reveal">
        <div class="fil-visual <?= $c['cls'] ?>">
          <h3><?= e($t["fl{$k}_t"]) ?></h3>
          <div class="fs"><?= e($t["fl{$k}_s"]) ?></div>
          <div class="layers" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
        </div>
        <div class="fil-body">
          <p class="desc"><?= e($t["fld{$k}"]) ?></p>
          <div class="fc-label"><?= e($t['fl_choose']) ?></div>
          <ul>
            <?php foreach ($t["fl{$k}_items"] as $item): ?>
            <li><?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="fl-rule reveal"><?= e($t['fl_d']) ?></div>

    <div class="more-card reveal">
      <div>
        <h3><?= e($t['fil_note_t']) ?></h3>
        <p><?= e($t['fil_note_d']) ?></p>
      </div>
      <a class="btn btn-primary" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg>
        <?= e($t['ig_btn']) ?>
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
