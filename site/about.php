<?php
$page = 'about';
require __DIR__ . '/includes/config.php';
$page_title = seo_title('about', $t['about_title']);
$page_meta  = seo_desc('about', $t['about_meta']);
$page_schema = 'AboutPage';
schema_breadcrumbs([[$t['nav_about'], '/haqqimizda/']]);
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_about']) ?></span>
    </div>
    <h1><?= e($t['about_h']) ?></h1>
  </div>
</section>

<section class="section about-page" style="padding-top:72px">
  <div class="container">
    <div class="about-grid">
      <div class="about-art reveal">
        <div class="about-ph">
          <img src="https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1712262810_3339024920686659731_3523099162-3-600x600.jpg" alt="Vanilla Cake" width="600" height="600" fetchpriority="high">
        </div>
        <div class="about-stamp">Made with love</div>
      </div>
      <div class="text">
        <span class="eyebrow"><?= e($t['about_eyebrow']) ?></span>
        <h2 style="margin:14px 0 22px"><?= e($t['about_t']) ?></h2>
        <p><?= e($t['about_full_p1']) ?></p>
        <p><?= e($t['about_full_p2']) ?></p>
        <p><?= e($t['about_full_p3']) ?></p>
        <div class="stats">
          <div class="stat"><b>850+</b><span><?= e($t['stat1']) ?></span></div>
          <div class="stat"><b>1650+</b><span><?= e($t['stat2']) ?></span></div>
          <div class="stat"><b>120+</b><span><?= e($t['stat3']) ?></span></div>
        </div>
      </div>
    </div>

    <div class="values">
      <div class="value-card reveal">
        <span class="num">01</span>
        <h3><?= e($t['about_v1t']) ?></h3>
        <p><?= e($t['about_v1d']) ?></p>
      </div>
      <div class="value-card reveal">
        <span class="num">02</span>
        <h3><?= e($t['about_v2t']) ?></h3>
        <p><?= e($t['about_v2d']) ?></p>
      </div>
      <div class="value-card reveal">
        <span class="num">03</span>
        <h3><?= e($t['about_v3t']) ?></h3>
        <p><?= e($t['about_v3d']) ?></p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
