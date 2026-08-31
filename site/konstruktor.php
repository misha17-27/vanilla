<?php
$page = 'konstr';
require __DIR__ . '/includes/config.php';
$page_title = $t['k_title'];
$page_meta  = $t['k_meta'];
require __DIR__ . '/includes/header.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet">
<?php
$sizeOpts = $t['sizes_opt_bento'];
$stickers = [
    ['bobo',    '/assets/img/stickers/bobo.png'],
    ['traktor', '/assets/img/stickers/traktor.png'],
    ['krot',    '/assets/img/stickers/krot.png'],
];
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="/"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['k_h']) ?></span>
    </div>
    <h1><?= e($t['k_h']) ?></h1>
    <p class="lead"><?= e($t['k_d']) ?></p>
  </div>
</section>

<section class="catalog">
  <div class="container">
    <div class="k-layout">

      <!-- Превью -->
      <div class="k-preview reveal in">
        <div class="k-box">
          <div class="k-cake" id="k-cake" style="--cream:#FDFBF7">
            <img id="k-sticker" src="" alt="" hidden draggable="false">
            <div class="k-text" id="k-text-view"></div>
          </div>
        </div>
        <p class="k-hint"><?= e($t['k_hint']) ?></p>
      </div>

      <!-- Панель -->
      <div class="k-panel">
        <div class="k-group">
          <div class="k-lbl"><?= e($t['k_cream']) ?></div>
          <div class="k-swatches" id="k-creams">
            <?php foreach ($t['k_colors'] as $i => $c): ?>
            <button type="button" class="k-sw<?= $i === 0 ? ' on' : '' ?>" style="--c:<?= e($c[1]) ?>" data-color="<?= e($c[1]) ?>" data-name="<?= e($c[0]) ?>" title="<?= e($c[0]) ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="k-group">
          <div class="k-lbl"><?= e($t['k_img']) ?></div>
          <div class="k-stickers" id="k-stickers">
            <button type="button" class="k-st on" data-src="" title="<?= e($t['k_img_none']) ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m5 5 14 14M19 5 5 19"/></svg>
            </button>
            <?php foreach ($stickers as $s): ?>
            <button type="button" class="k-st" data-src="<?= e($s[1]) ?>"><img src="<?= e($s[1]) ?>" alt="" loading="lazy"></button>
            <?php endforeach; ?>
            <button type="button" class="k-st k-own" id="k-own" title="<?= e($t['k_img_own']) ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8a2 2 0 0 1 2-2h2l1.5-2h7L17 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="3.6"/></svg>
            </button>
            <input type="file" id="k-file" accept="image/jpeg,image/png,image/webp" hidden>
          </div>
          <div class="k-slider" id="k-size-row" hidden>
            <span class="k-lbl-s"><?= e($t['k_img_size']) ?></span>
            <input type="range" id="k-size" min="25" max="85" value="55">
          </div>
        </div>

        <div class="k-group">
          <div class="k-lbl"><?= e($t['k_text']) ?></div>
          <input class="txt" type="text" id="k-text" placeholder="<?= e($t['f_text_ph']) ?>" maxlength="40">
          <div class="k-lbl" style="margin-top:12px"><?= e($t['k_text_color']) ?></div>
          <div class="k-swatches" id="k-tcolors">
            <?php foreach ($t['k_tcolors'] as $i => $c): ?>
            <button type="button" class="k-sw<?= $i === 0 ? ' on' : '' ?>" style="--c:<?= e($c[1]) ?>" data-color="<?= e($c[1]) ?>" data-name="<?= e($c[0]) ?>" title="<?= e($c[0]) ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="k-group">
          <div class="prod-price" id="prod-price"><?= e($sizeOpts[0][1]) ?> ₼</div>
          <div class="prod-opts" style="margin-top:14px">
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
          </div>
          <div class="prod-ctas" style="margin-top:20px">
            <button type="button" class="btn btn-primary" id="prod-order">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>
              <?= e($t['pd_order']) ?>
            </button>
          </div>
          <div class="prod-note" style="margin-top:16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="18" height="17" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
            <span><?= e($t['pd_note']) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
$modalName = $t['k_custom_name'];
$modalImg  = '';
require __DIR__ . '/includes/order-modal.php';
?>

<script>
window.PROD_CFG = <?= json_encode([
    'intro'   => $t['k_wa_intro'],
    'labels'  => [
        'size' => $t['opt_size'], 'sponge' => $t['opt_sponge'], 'fill' => $t['opt_fill'],
        'date' => $t['opt_date'], 'time' => $t['opt_time'], 'dl' => $t['opt_dl'], 'text' => $t['f_text'],
        'address' => $t['f_address'], 'name' => $t['f_name'], 'phone' => $t['f_phone'],
        'recipient' => $t['f_recipient'],
    ],
    'locale'  => $lang,
    'sizes'   => $sizeOpts,
    'sponges' => [
        [$t['fl1_t'], $t['fl1_items']],
        [$t['fl2_t'], $t['fl2_items']],
        [$t['fl3_t'], $t['fl3_items']],
    ],
    'wa'      => 'https://wa.me/' . WA_NUMBER . '?text=',
    'purl'    => CANON_HOST . '/konstruktor/',
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
    'creamLbl' => $t['k_cream_lbl'],
], JSON_UNESCAPED_UNICODE) ?>;

// ===== Конструктор торта =====
(function () {
  var cake = document.getElementById('k-cake');
  var stickerEl = document.getElementById('k-sticker');
  var textView = document.getElementById('k-text-view');
  var textInp = document.getElementById('k-text');
  var sizeRange = document.getElementById('k-size');
  var sizeRow = document.getElementById('k-size-row');
  var fileInp = document.getElementById('k-file');
  var state = {
    cream: PROD_CFG && document.querySelector('#k-creams .k-sw') ? document.querySelector('#k-creams .k-sw').dataset.color : '#FDFBF7',
    creamName: document.querySelector('#k-creams .k-sw') ? document.querySelector('#k-creams .k-sw').dataset.name : '',
    src: '', ownFile: null,
    x: 0, y: -0.15, scale: 0.55,
    tx: 0, ty: 0.62,
    text: '', tcolor: document.querySelector('#k-tcolors .k-sw') ? document.querySelector('#k-tcolors .k-sw').dataset.color : '#E0527F',
    tcolorName: document.querySelector('#k-tcolors .k-sw') ? document.querySelector('#k-tcolors .k-sw').dataset.name : ''
  };
  function apply() {
    cake.style.setProperty('--cream', state.cream);
    var d = cake.clientWidth;
    if (state.src) {
      stickerEl.src = state.src;
      stickerEl.hidden = false;
      var s = state.scale * d;
      stickerEl.style.width = s + 'px';
      stickerEl.style.left = (d / 2 + state.x * d / 2 - s / 2) + 'px';
      stickerEl.style.top = (d / 2 + state.y * d / 2 - s / 2) + 'px';
      sizeRow.hidden = false;
    } else {
      stickerEl.hidden = true;
      sizeRow.hidden = true;
    }
    textView.textContent = state.text;
    textView.style.color = state.tcolor;
    textView.style.left = (d / 2 + state.tx * d / 2) + 'px';
    textView.style.top = (d / 2 + state.ty * d / 2) + 'px';
  }
  // Свотчи
  function swatchGroup(id, cb) {
    var box = document.getElementById(id);
    box.addEventListener('click', function (e) {
      var b = e.target.closest('.k-sw');
      if (!b) return;
      box.querySelectorAll('.k-sw').forEach(function (x) { x.classList.toggle('on', x === b); });
      cb(b.dataset.color, b.dataset.name);
    });
  }
  swatchGroup('k-creams', function (c, n) { state.cream = c; state.creamName = n; apply(); });
  swatchGroup('k-tcolors', function (c, n) { state.tcolor = c; state.tcolorName = n; apply(); });
  // Стикеры
  document.getElementById('k-stickers').addEventListener('click', function (e) {
    var b = e.target.closest('.k-st');
    if (!b) return;
    if (b.id === 'k-own') { fileInp.click(); return; }
    document.querySelectorAll('.k-st').forEach(function (x) { x.classList.toggle('on', x === b); });
    state.src = b.dataset.src || '';
    state.ownFile = null;
    state.x = 0; state.y = -0.15;
    apply();
  });
  fileInp.addEventListener('change', function () {
    var f = fileInp.files && fileInp.files[0];
    if (!f) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1 || f.size > 8 * 1024 * 1024) return;
    state.src = URL.createObjectURL(f);
    state.ownFile = f;
    state.x = 0; state.y = -0.15;
    document.querySelectorAll('.k-st').forEach(function (x) { x.classList.toggle('on', x.id === 'k-own'); });
    apply();
  });
  sizeRange.addEventListener('input', function () { state.scale = sizeRange.value / 100; apply(); });
  textInp.addEventListener('input', function () {
    state.text = textInp.value.replace(/[\u0000-\u001F\u007F]/g, ' ').slice(0, 40);
    apply();
  });
  // Перетаскивание картинки и надписи
  function makeDraggable(el, kx, ky) {
    var drag = null;
    el.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      el.setPointerCapture(e.pointerId);
      drag = { px: e.clientX, py: e.clientY, x: state[kx], y: state[ky] };
    });
    el.addEventListener('pointermove', function (e) {
      if (!drag) return;
      var d = cake.clientWidth / 2;
      state[kx] = Math.max(-0.9, Math.min(0.9, drag.x + (e.clientX - drag.px) / d));
      state[ky] = Math.max(-0.9, Math.min(0.9, drag.y + (e.clientY - drag.py) / d));
      apply();
    });
    el.addEventListener('pointerup', function () { drag = null; });
    el.addEventListener('pointercancel', function () { drag = null; });
  }
  makeDraggable(stickerEl, 'x', 'y');
  makeDraggable(textView, 'tx', 'ty');
  window.addEventListener('resize', apply);
  apply();

  // Рендер макета в canvas -> загрузка -> ссылка в WhatsApp
  function loadImg(src) {
    return new Promise(function (res, rej) {
      var im = new Image();
      im.onload = function () { res(im); };
      im.onerror = rej;
      im.src = src;
    });
  }
  async function renderAndUpload() {
    try {
      await document.fonts.load('600 80px Caveat');
      var S = 900, R = 400, cx = S / 2, cy = S / 2;
      var cv = document.createElement('canvas');
      cv.width = S; cv.height = S;
      var g = cv.getContext('2d');
      g.fillStyle = '#F3F0EB';
      g.fillRect(0, 0, S, S);
      // торт
      var grad = g.createRadialGradient(cx - R * 0.3, cy - R * 0.35, R * 0.2, cx, cy, R * 1.05);
      grad.addColorStop(0, 'rgba(255,255,255,0.55)');
      grad.addColorStop(0.55, 'rgba(255,255,255,0)');
      grad.addColorStop(1, 'rgba(0,0,0,0.10)');
      g.save();
      g.shadowColor = 'rgba(60,45,35,.25)';
      g.shadowBlur = 40;
      g.shadowOffsetY = 18;
      g.fillStyle = state.cream;
      g.beginPath(); g.arc(cx, cy, R, 0, 7); g.fill();
      g.restore();
      g.fillStyle = grad;
      g.beginPath(); g.arc(cx, cy, R, 0, 7); g.fill();
      // бортик
      g.strokeStyle = 'rgba(0,0,0,0.06)';
      g.lineWidth = 10;
      g.beginPath(); g.arc(cx, cy, R - 6, 0, 7); g.stroke();
      // картинка (клип по кругу)
      if (state.src) {
        var im = await loadImg(state.src);
        var s = state.scale * 2 * R;
        var ix = cx + state.x * R - s / 2;
        var iy = cy + state.y * R - s / 2;
        var ratio = im.naturalWidth / im.naturalHeight;
        var w = s, h = s;
        if (ratio > 1) h = s / ratio; else w = s * ratio;
        g.save();
        g.beginPath(); g.arc(cx, cy, R - 12, 0, 7); g.clip();
        g.drawImage(im, ix + (s - w) / 2, iy + (s - h) / 2, w, h);
        g.restore();
      }
      // надпись
      if (state.text) {
        var fs = 86;
        g.font = '600 ' + fs + 'px Caveat, cursive';
        while (g.measureText(state.text).width > R * 1.55 && fs > 30) {
          fs -= 4;
          g.font = '600 ' + fs + 'px Caveat, cursive';
        }
        g.fillStyle = state.tcolor;
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        if (state.tcolor.toLowerCase() === '#ffffff') {
          g.shadowColor = 'rgba(0,0,0,.35)'; g.shadowBlur = 6;
        }
        g.fillText(state.text, cx + state.tx * R, cy + state.ty * R);
        g.shadowBlur = 0;
      }
      var blob = await new Promise(function (r) { cv.toBlob(r, 'image/jpeg', 0.9); });
      var fd = new FormData();
      fd.append('photo', new File([blob], 'cake-design.jpg', { type: 'image/jpeg' }));
      fd.append('csrf', PROD_CFG.csrf);
      var res = await fetch(PROD_CFG.upload, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
      if (res && res.ok && res.url && window.__setDesign) {
        window.__setDesign(res.url.indexOf('http') === 0 ? res.url : location.origin + res.url);
      }
    } catch (e) { /* макет не критичен — заказ уйдёт без ссылки */ }
  }
  // Доп. строки о дизайне в сообщение + рендер при открытии попапа
  var orderBtn = document.getElementById('prod-order');
  orderBtn.addEventListener('click', function () {
    if (window.__setExtras) {
      var extras = [PROD_CFG.creamLbl + ': ' + state.creamName];
      if (state.text) extras.push(PROD_CFG.labels.text + ': «' + state.text + '» (' + state.tcolorName + ')');
      window.__setExtras(extras);
    }
    var send = document.getElementById('modal-send');
    send.classList.add('busy');
    renderAndUpload().then(function () { send.classList.remove('busy'); });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
