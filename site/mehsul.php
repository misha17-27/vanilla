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
$og_image   = str_starts_with($prod['img'], 'http') ? $prod['img'] : CANON_HOST . $prod['img'];

// категория для крошек и раздела «похожие»
$catMap = [
    'bento'  => ['/bolme/bento-tort/',        $t['nav_bento'],  'bento'],
    'bantik' => ['/bolme/bento-tort/#bantik', $t['bantik_h'],   'bento'],
    'set'    => ['/bolme/bento-tort/#sets',   $t['sets_h'],     'bento'],
    'ctg'    => ['/bolme/cake-to-go/',        $t['nav_ctg'],    'ctg'],
];
[$catUrl, $catLabel, $navSlug] = $catMap[$prod['type']];
$page = $navSlug;

$sizeOpts = $t['sizes_opt_' . $prod['type']];

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
        <div class="prod-price" id="prod-price"><?= e($sizeOpts[0][1]) ?> ₼</div>

        <div class="prod-opts">
          <div class="opt-row">
            <label for="opt-size"><?= e($t['opt_size']) ?></label>
            <select id="opt-size">
              <?php foreach ($sizeOpts as $i => $o): ?>
              <option value="<?= $i ?>"><?= e($o[0]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="opt-row">
            <label><?= e($t['opt_sponge']) ?> · <?= e($t['opt_fill']) ?></label>
            <div class="duo">
              <select id="opt-sponge">
                <?php foreach ([1, 2, 3] as $i): ?>
                <option value="<?= $i ?>"><?= e($t["fl{$i}_t"]) ?></option>
                <?php endforeach; ?>
              </select>
              <select id="opt-fill">
                <?php foreach ($t['fl1_items'] as $item): ?>
                <option><?= e($item) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="opt-row">
            <label for="f-text"><?= e($t['f_text']) ?></label>
            <input class="txt" type="text" id="f-text" placeholder="<?= e($t['f_text_ph']) ?>" maxlength="80">
          </div>
        </div>

        <div class="up-box" id="up-box">
          <input type="file" id="up-input" accept="image/jpeg,image/png,image/webp" hidden>
          <button type="button" class="up-idle" id="up-idle">
            <span class="up-ico">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8a2 2 0 0 1 2-2h2l1.5-2h7L17 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="3.6"/></svg>
            </span>
            <span class="up-txt">
              <b><?= e($t['up_t']) ?></b>
              <small><?= e($t['up_d']) ?></small>
              <small class="up-hint"><?= e($t['up_hint']) ?></small>
            </span>
          </button>
          <div class="up-load" id="up-load" hidden><span class="up-spin"></span><?= e($t['up_loading']) ?></div>
          <div class="up-done" id="up-done" hidden>
            <img id="up-thumb" alt="" width="56" height="56">
            <span class="up-txt"><b><?= e($t['up_ok']) ?></b><small><?= e($t['up_ok_d']) ?></small></span>
            <button type="button" class="up-remove" id="up-remove" aria-label="<?= e($t['up_remove']) ?>">×</button>
          </div>
          <div class="up-err" id="up-err" hidden></div>
        </div>

        <div class="prod-ctas">
          <button type="button" class="btn btn-primary" id="prod-order">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
            <?= e($t['pd_order']) ?>
          </button>
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

    <!-- Информация о товаре: вертикальные открытые секции -->
    <div class="pd-secs reveal">
      <section class="pd-sec">
        <h3><?= e($t['tab_desc']) ?></h3>
        <p><?= e($t['pd_desc']) ?></p>
        <ul class="tp-sizes">
          <?php foreach ($sizeOpts as $o): ?>
          <li><span><?= e($o[0]) ?></span><b><?= e($o[1]) ?> ₼</b></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($prod['type'] === 'ctg'): ?>
        <p class="tp-strong" style="margin-top:14px"><?= e($t['sizes_ctg_note']) ?></p>
        <?php endif; ?>
      </section>
      <section class="pd-sec">
        <h3><?= e($t['tab_fill']) ?></h3>
        <div class="tp-fill-grid">
          <?php foreach ([1, 2, 3] as $i): ?>
          <div class="tp-fill">
            <b><?= e($t["fl{$i}_t"]) ?> <?= e($t["fl{$i}_s"]) ?></b>
            <?php filling_rows($i); ?>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="margin-top:20px"><?= e($t['fl_d']) ?> <a class="tp-link" href="/terkibler/"><?= e($t['nav_fillings']) ?> →</a></p>
      </section>
      <section class="pd-sec">
        <h3><?= e($t['tab_time']) ?></h3>
        <p><?= e($t['pd_time']) ?></p>
      </section>
      <section class="pd-sec">
        <h3><?= e($t['tab_del']) ?></h3>
        <p><?= e($t['pd_del']) ?></p>
      </section>
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

<?php
$modalName = $name;
$modalImg  = $prod['img'];
require __DIR__ . '/includes/order-modal.php';
?>

<script>
window.PROD_CFG = <?= json_encode([
    'intro'   => sprintf($t['wa_msg_p'], $name),
    'labels'  => [
        'size' => $t['opt_size'], 'sponge' => $t['opt_sponge'], 'fill' => $t['opt_fill'],
        'date' => $t['opt_date'], 'time' => $t['opt_time'], 'dl' => $t['opt_dl'], 'text' => $t['f_text'],
        'address' => $t['f_address'], 'name' => $t['f_name'], 'phone' => $t['f_phone'],
        'recipient' => $t['f_recipient'],
    ],
    'valFill'  => $t['val_fill'],
    'mapSearching' => $t['map_searching'],
    'pointLbl' => $t['wa_point'],
    'valPhone' => $t['val_phone'],
    'locale'  => $lang,
    'sizes'   => $sizeOpts,
    'sponges' => [
        [$t['fl1_t'], $t['fl1_items']],
        [$t['fl2_t'], $t['fl2_items']],
        [$t['fl3_t'], $t['fl3_items']],
    ],
    'wa'      => 'https://wa.me/' . WA_NUMBER . '?text=',
    'purl'    => CANON_HOST . product_url($prod),
    'source'  => 'product',
    'orderName' => $name,
    'linkLbl' => $t['wa_link_lbl'],
    'upload'  => '/upload-design.php',
    'csrf'    => $_SESSION['csrf'],
    'design'  => $t['wa_design'],
    'errors'  => [
        'type'    => $t['up_e_type'],
        'size'    => $t['up_e_size'],
        'rate'    => $t['up_e_rate'],
        'generic' => $t['up_e_generic'],
    ],
], JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
