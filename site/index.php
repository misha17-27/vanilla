<?php
$page = 'index';
require_once __DIR__ . '/includes/config.php';
$page_title = seo_title('home', $t['home_title']);
$page_meta  = seo_desc('home', $t['home_meta']);
require __DIR__ . '/includes/header.php';
$page_schema = 'WebPage';
$bento  = products_of('bento');
$bantik = products_of('bantik');
$sets   = products_of('set');
$ctg    = products_of('ctg');
?>

<!-- Hero slider: по слайду на категорию -->
<?php
$heroSlides = [
    ['eye' => $t['hero_eyebrow'], 'title' => $t['hero_h'],  'lead' => $t['hero_lead'], 'url' => '/bolme/bento-tort/',        'btn' => $t['hero_cta2'],     'img' => 'https://vanilla.az/wp-content/uploads/2025/07/vanilla_cake_az_1715803570_3368726971637270283_3523099162-3-600x600.jpg', 'h1' => true],
    ['eye' => $t['bantik_h'],     'title' => $t['hs2_t'],   'lead' => $t['hs2_d'],     'url' => cat_url(categories()['bantik']), 'btn' => $t['btn_all_bento'], 'img' => '/assets/img/bantik-blush.jpg'],
    ['eye' => $t['sets_h'],       'title' => $t['hs3_t'],   'lead' => $t['hs3_d'],     'url' => cat_url(categories()['set']),   'btn' => $t['btn_all_bento'], 'img' => '/assets/img/set-white.jpg'],
    ['eye' => $t['sec_ctg_t'],    'title' => $t['hs4_t'],   'lead' => $t['hs4_d'],     'url' => '/bolme/cake-to-go/',        'btn' => $t['btn_all_ctg'],   'img' => '/assets/img/ctg-minimal.jpg'],
];
?>
<section class="hero">
  <div class="hero-track" id="hero-track">
    <?php foreach ($heroSlides as $si => $s): ?>
    <div class="hslide<?= $si === 0 ? ' on' : '' ?>">
      <div class="container">
        <div>
          <span class="eyebrow"><?= e($s['eye']) ?></span>
          <?php if (!empty($s['h1'])): ?>
          <h1 class="hero-title" style="margin-top:18px"><?= $s['title'] ?></h1>
          <?php else: ?>
          <div class="hero-title" style="margin-top:18px"><?= $s['title'] ?></div>
          <?php endif; ?>
          <p class="lead"><?= e($s['lead']) ?></p>
          <div class="hero-ctas">
            <a class="btn btn-primary" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
              <?= e($t['btn_wa']) ?>
            </a>
            <a class="btn btn-ghost" href="<?= e($s['url']) ?>"><?= e($s['btn']) ?></a>
          </div>
          <div class="trust">
            <span class="stars">★★★★★</span>
            <span><?= $t['hero_trust'] ?></span>
          </div>
        </div>
        <div class="hero-art">
          <div class="hero-deco d1"></div>
          <div class="hero-deco d2"></div>
          <div class="hero-ph">
            <img src="<?= e($s['img']) ?>" alt="<?= e($s['eye']) ?>" width="600" height="600" <?= $si === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
          </div>
          <div class="hero-badge">
            <span class="dot">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="17" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
            </span>
            <span><b><?= e($t['hero_badge_t']) ?></b><small><?= e($t['hero_badge_d']) ?></small></span>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <button class="hero-nav prev" id="hero-prev" aria-label="Prev">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 6-6 6 6 6"/></svg>
  </button>
  <button class="hero-nav next" id="hero-next" aria-label="Next">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
  </button>
  <div class="hero-dots" id="hero-dots">
    <?php foreach ($heroSlides as $si => $s): ?>
    <button<?= $si === 0 ? ' class="on"' : '' ?> aria-label="<?= $si + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</section>

<!-- USP -->
<section class="usp">
  <div class="container">
    <div class="usp-item reveal">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10Z"/><path d="M2 21c0-3 1.9-5.5 3.5-7"/></svg></span>
      <div><h3><?= e($t['u1t']) ?></h3><p><?= e($t['u1d']) ?></p></div>
    </div>
    <div class="usp-item reveal">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.46.32-.85.73-1.04a4 4 0 0 0-2.14-7.59 5 5 0 0 0-9.18 0 4 4 0 0 0-2.14 7.59c.41.19.73.58.73 1.04V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/></svg></span>
      <div><h3><?= e($t['u2t']) ?></h3><p><?= e($t['u2d']) ?></p></div>
    </div>
    <div class="usp-item reveal">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3V6a1 1 0 0 1 1-1h9v12"/><path d="M13 8h4l3 4v5h-2"/><circle cx="7.5" cy="17.5" r="2"/><circle cx="16.5" cy="17.5" r="2"/></svg></span>
      <div><h3><?= e($t['u3t']) ?></h3><p><?= e($t['u3d']) ?></p></div>
    </div>
    <div class="usp-item reveal">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="9" width="18" height="12" rx="2"/><path d="M3 13h18M12 9v12M12 9c-2 0-4-1-4-3a2 2 0 0 1 4 0M12 9c2 0 4-1 4-3a2 2 0 0 0-4 0"/></svg></span>
      <div><h3><?= e($t['u4t']) ?></h3><p><?= e($t['u4d']) ?></p></div>
    </div>
  </div>
</section>

<!-- Occasions -->
<section class="section" style="padding-bottom:80px">
  <div class="container">
    <div class="sec-head center">
      <span class="eyebrow">Instagram</span>
      <h2><?= e($t['occ_t']) ?></h2>
      <p><?= e($t['occ_d']) ?></p>
    </div>
    <div class="occasions reveal">
      <a class="occ" href="<?= e(cat_url(categories()['bantik'])) ?>">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2.2"/><path d="M10.5 11 4 7.5a1.8 1.8 0 0 0-2.6 1.9L3 15l7-2.5M13.5 11 20 7.5a1.8 1.8 0 0 1 2.6 1.9L21 15l-7-2.5M10 13.5 6.5 20M14 13.5 17.5 20"/></svg></span>
        <span><?= e($t['occ1']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="10" r="3.5"/><path d="M8 13.5V20M5.5 17h5"/><circle cx="16.5" cy="9" r="3.5"/><path d="m19 6.5 3-3M22 7V3.5h-3.5"/></svg></span>
        <span><?= e($t['occ2']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><circle cx="9" cy="10.5" r=".6" fill="currentColor"/><circle cx="15" cy="10.5" r=".6" fill="currentColor"/><path d="M8.5 14.5c1 1.2 2.2 1.8 3.5 1.8s2.5-.6 3.5-1.8"/><path d="M12 3.5c1 .8 1.4 1.8 1 3"/></svg></span>
        <span><?= e($t['occ3']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12v9M12 21c0-3-2-5-5-5M12 21c0-3 2-5 5-5"/><circle cx="12" cy="7.5" r="2"/><circle cx="8.7" cy="5.8" r="2"/><circle cx="15.3" cy="5.8" r="2"/><circle cx="10" cy="9.4" r="2"/><circle cx="14" cy="9.4" r="2"/></svg></span>
        <span><?= e($t['occ4']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20.5s-8-5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9.5c0 6-8 11-8 11Z"/></svg></span>
        <span><?= e($t['occ5']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 3.5 4.5H13l3.5 4.5H14l3.5 4.5h-11L10 12H7.5L11 7.5H8.5L12 3ZM12 16.5V21"/></svg></span>
        <span><?= e($t['occ6']) ?></span>
      </a>
      <a class="occ" href="<?= e(cat_url(categories()['set'])) ?>">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="14" width="16" height="6" rx="2"/><rect x="6" y="9" width="12" height="5" rx="2"/><rect x="8" y="4" width="8" height="5" rx="2"/></svg></span>
        <span><?= e($t['occ7']) ?></span>
      </a>
      <a class="occ" href="<?= IG_URL ?>" target="_blank" rel="noopener">
        <span class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 8h11l-1.2 13h-8.6L6.5 8Z"/><path d="M5.5 8h13M9 4.5h6l1 3.5"/><path d="M9.5 12c.8 1 1.7 1.5 2.5 1.5s1.7-.5 2.5-1.5"/></svg></span>
        <span><?= e($t['occ8']) ?></span>
      </a>
    </div>
  </div>
</section>

<!-- Bento preview -->
<section class="section band-cream">
  <div class="container">
    <div class="head-row">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e($t['sec_bento_t']) ?></h2>
        <p><?= e($t['sec_bento_d']) ?></p>
      </div>
      <a class="btn btn-ghost" href="/bolme/bento-tort/"><?= e($t['btn_all_bento']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($bento, 0, 16) as $p) product_card($p); ?>
    </div>
    <div class="center sec-cta">
      <a class="btn btn-ghost" href="/bolme/bento-tort/"><?= e($t['btn_all_bento']) ?></a>
    </div>
  </div>
</section>

<!-- Bantik preview -->
<section class="section">
  <div class="container">
    <div class="head-row">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e($t['bantik_h']) ?></h2>
        <p><?= e($t['bantik_d']) ?></p>
      </div>
      <a class="btn btn-ghost" href="<?= e(cat_url(categories()['bantik'])) ?>"><?= e($t['btn_all_bento']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($bantik, 0, 8) as $p) product_card($p); ?>
    </div>
  </div>
</section>

<!-- Sets preview -->
<section class="section band-cream">
  <div class="container">
    <div class="head-row">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e($t['sets_h']) ?></h2>
        <p><?= e($t['sets_d']) ?></p>
      </div>
      <a class="btn btn-ghost" href="<?= e(cat_url(categories()['set'])) ?>"><?= e($t['btn_all_bento']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($sets, 0, 8) as $p) product_card($p); ?>
    </div>
  </div>
</section>

<?php foreach (own_categories() as $ownCat):
    if (in_array($ownCat['key'], ['bantik', 'set'], true)) continue;   // у них свои секции выше
    $ownItems = products_of($ownCat['key']); if (!$ownItems) continue; ?>
<section class="section">
  <div class="container">
    <div class="head-row">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e(cat_name($ownCat['key'])) ?></h2>
        <?php $ownDesc = trim((string)($ownCat['desc_' . $lang] ?? $ownCat['desc'] ?? '')); ?>
        <?php if ($ownDesc !== ''): ?><p><?= e($ownDesc) ?></p><?php endif; ?>
      </div>
      <a class="btn btn-ghost" href="<?= e(cat_url($ownCat)) ?>"><?= e($t['btn_all_bento']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($ownItems, 0, 8) as $p) product_card($p); ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<!-- Cake to go preview -->
<section class="section">
  <div class="container">
    <div class="head-row">
      <div class="sec-head">
        <span class="eyebrow">Vanilla</span>
        <h2><?= e($t['sec_ctg_t']) ?></h2>
        <p><?= e($t['sec_ctg_d']) ?></p>
      </div>
      <a class="btn btn-ghost" href="/bolme/cake-to-go/"><?= e($t['btn_all_ctg']) ?></a>
    </div>
    <div class="pgrid">
      <?php foreach (array_slice($ctg, 0, 16) as $p) product_card($p); ?>
    </div>
    <div class="center sec-cta">
      <a class="btn btn-ghost" href="/bolme/cake-to-go/"><?= e($t['btn_all_ctg']) ?></a>
    </div>
  </div>
</section>

<!-- Sizes -->
<section class="section band-blue">
  <div class="container">
    <div class="sec-head center">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e($t['sizes_t']) ?></h2>
      <p><?= e($t['sizes_d']) ?></p>
    </div>
    <div class="sizes-wrap">
      <div class="size-card reveal">
        <h3><?= e($t['sizes_bento_h']) ?></h3>
        <div class="size-row"><span class="w"><?= e($t['size_b1_w']) ?></span><span class="p"><?= e($t['size_b1_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b1_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b2_w']) ?></span><span class="p"><?= e($t['size_b2_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b2_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_b3_w']) ?></span><span class="p"><?= e($t['size_b3_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_b3_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_bk_w']) ?></span><span class="p"><?= e($t['size_bk_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_bk_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_st_w']) ?></span><span class="p"><?= e($t['size_st_p']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_st_c']) ?></span></div>
      </div>
      <div class="size-card reveal">
        <h3><?= e($t['sizes_ctg_h']) ?></h3>
        <div class="size-row"><span class="w"><?= e($t['size_c1_w']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_c1_c']) ?></span></div>
        <div class="size-row"><span class="w"><?= e($t['size_c2_w']) ?></span><span class="dots"></span><span class="c"><?= e($t['size_c2_c']) ?></span></div>
        <div class="size-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span><?= e($t['sizes_ctg_note']) ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Flavors -->
<section class="section">
  <div class="container">
    <div class="sec-head center">
      <span class="eyebrow">Vanilla</span>
      <h2><?= e($t['fl_t']) ?></h2>
      <p><?= e($t['fl_d']) ?></p>
    </div>
    <div class="flavors">
      <?php foreach ([1, 2, 3] as $i): ?>
      <div class="flavor-card reveal">
        <div class="fc-head">
          <h3><?= e($t["fl{$i}_t"]) ?></h3>
          <div class="fc-sub"><?= e($t["fl{$i}_s"]) ?></div>
        </div>
        <div class="fc-label"><?= e($t['fl_choose']) ?></div>
        <?php filling_rows($i); ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="center sec-cta">
      <a class="btn btn-ghost" href="/terkibler/"><?= e($t['nav_fillings']) ?> →</a>
    </div>
  </div>
</section>

<!-- Chat: one message — one cake -->
<section class="section chat-sec">
  <div class="container chat-wrap">
    <div class="chat-card reveal">
      <div class="chat-head">
        <span class="ava">V</span>
        <div><b>Vanilla Cake</b><small><?= e($t['chat_online']) ?></small></div>
      </div>
      <div class="bub me"><?= e($t['cb1']) ?></div>
      <div class="bub shop"><?= e($t['cb2']) ?></div>
      <div class="bub me"><?= e($t['cb3']) ?></div>
      <div class="bub shop"><?= e($t['cb4']) ?></div>
    </div>
    <div class="chat-side">
      <div class="sec-head">
        <span class="eyebrow"><?= e($t['chat_tag']) ?></span>
        <h2><?= e($t['chat_t']) ?></h2>
        <p><?= e($t['chat_d']) ?></p>
      </div>
      <div class="chat-steps">
        <div class="chat-step"><span class="num">1</span><div><b><?= e($t['chat_s1t']) ?></b><p><?= e($t['chat_s1d']) ?></p></div></div>
        <div class="chat-step"><span class="num">2</span><div><b><?= e($t['chat_s2t']) ?></b><p><?= e($t['chat_s2d']) ?></p></div></div>
        <div class="chat-step"><span class="num">3</span><div><b><?= e($t['chat_s3t']) ?></b><p><?= e($t['chat_s3d']) ?></p></div></div>
      </div>
      <a class="btn btn-primary" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
        <?= e($t['chat_btn']) ?>
      </a>
    </div>
  </div>
</section>

<!-- About teaser -->
<section class="section about-sec band-cream">
  <div class="container">
    <div class="about-art reveal">
      <div class="about-ph">
        <img loading="lazy" src="/assets/img/ctg-princess.jpg" alt="Vanilla Cake — cake to go" width="480" height="480">
      </div>
      <div class="about-stamp">Made with love</div>
    </div>
    <div>
      <span class="eyebrow"><?= e($t['about_eyebrow']) ?></span>
      <h2 style="margin-top:14px"><?= e($t['about_t']) ?></h2>
      <p style="margin-top:18px"><?= e($t['about_p1']) ?></p>
      <p style="margin-top:14px"><?= e($t['about_p2']) ?></p>
      <div class="stats">
        <div class="stat"><b>850+</b><span><?= e($t['stat1']) ?></span></div>
        <div class="stat"><b>1650+</b><span><?= e($t['stat2']) ?></span></div>
        <div class="stat"><b>120+</b><span><?= e($t['stat3']) ?></span></div>
      </div>
      <a class="btn btn-ghost" href="/haqqimizda/"><?= e($t['about_btn']) ?></a>
    </div>
  </div>
</section>

<!-- Instagram feed -->
<?php $ig = json_decode((string)@file_get_contents(__DIR__ . '/data/instagram.json'), true); ?>
<?php if (!empty($ig['posts'])): ?>
<section class="section ig-sec">
  <div class="container">
    <div class="ig-head">
      <span class="ig-glyph">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg>
      </span>
      <h2 class="ig-handle"><a href="<?= e($ig['profile']) ?>" target="_blank" rel="noopener"><?= e($ig['handle']) ?></a></h2>
      <p><?= e($t['ig_d']) ?></p>
    </div>
    <div class="ig-grid reveal">
      <?php foreach ($ig['posts'] as $p): ?>
      <a class="ig-tile" href="<?= e($p['url']) ?>" target="_blank" rel="noopener" aria-label="Instagram">
        <img loading="lazy" src="<?= e(asset($p['img'])) ?>" alt="Vanilla Cake — Instagram" width="480" height="480">
        <span class="ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg></span>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="center sec-cta">
      <a class="btn btn-primary" href="<?= e($ig['profile']) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg>
        <?= e($t['ig_btn']) ?>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
