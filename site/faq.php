<?php
$page = 'faq';
require_once __DIR__ . '/includes/config.php';
$page_title = seo_title('faq', $t['faq_title']);
$page_meta  = seo_desc('faq', $t['faq_meta']);
$items = ['f1', 'f2', 'f7', 'f3', 'f4', 'f5', 'f6'];
$page_schema = 'FAQPage';
$page_schema_extra = [
    'mainEntity' => array_map(fn($k) => [
        '@type'          => 'Question',
        'name'           => $t[$k . 'q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $t[$k . 'a']],
    ], $items),
];
schema_breadcrumbs([[$t['nav_faq'], '/faq/']]);
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_faq']) ?></span>
    </div>
    <h1><?= e($t['faq_h']) ?></h1>
    <p class="lead"><?= e($t['faq_d']) ?></p>
  </div>
</section>

<section class="catalog band-cream">
  <div class="container">
    <div class="faq-list">
      <?php foreach ($items as $i => $key): ?>
      <details<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= e($t[$key . 'q']) ?></summary>
        <div class="a"><?= e($t[$key . 'a']) ?></div>
      </details>
      <?php endforeach; ?>
    </div>
    <div class="center" style="margin-top:44px">
      <a class="btn btn-primary" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
        <?= e($t['btn_wa']) ?>
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
