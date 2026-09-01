<?php
$page = 'konstr';
require_once __DIR__ . '/includes/config.php';
$page_title = seo_title('konstruktor', $t['k_title']);
$page_meta  = seo_desc('konstruktor', $t['k_meta']);
schema_breadcrumbs([[$t['nav_konstr'], '/konstruktor/']]);
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
          <div class="k-cake" id="k-cake" style="--cream:#FDFBF7"></div>
          <div class="k-toast" id="k-toast" hidden></div>
        </div>
        <div class="k-tools" id="k-tools">
          <div class="k-tools-empty" id="k-tools-empty"><?= e($t['k_sel_none']) ?></div>
          <div class="k-tools-on" id="k-tools-on" hidden>
            <span class="k-lbl-s"><?= e($t['k_sel_size']) ?></span>
            <input type="range" id="k-size" min="20" max="130" value="55">
            <button type="button" class="k-del" id="k-del"><?= e($t['k_del']) ?></button>
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
        </div>

        <div class="k-group">
          <div class="k-lbl"><?= e($t['k_text']) ?></div>
          <div class="k-texts" id="k-texts"></div>
          <button type="button" class="k-add" id="k-add-text"><?= e($t['k_add_text']) ?></button>
          <div class="k-lbl" style="margin-top:16px"><?= e($t['k_text_color']) ?></div>
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
    'purl'    => CANON_HOST . '/konstruktor/',
    'source'  => 'constructor',
    'orderName' => $t['k_h'],
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
    'textPh'   => $t['k_text_n'],
    'delLbl'   => $t['k_del'],
    'photoLbl' => $t['wa_photo'],
    'limitImg' => $t['k_limit_img'],
    'limitText' => $t['k_limit_text'],
], JSON_UNESCAPED_UNICODE) ?>;

// ===== Конструктор торта (слои: картинки + надписи) =====
(function () {
  var cake = document.getElementById('k-cake');
  var sizeRange = document.getElementById('k-size');
  var toolsOn = document.getElementById('k-tools-on');
  var toolsEmpty = document.getElementById('k-tools-empty');
  var delBtn = document.getElementById('k-del');
  var fileInp = document.getElementById('k-file');
  var textsBox = document.getElementById('k-texts');
  var addTextBtn = document.getElementById('k-add-text');
  var firstCream = document.querySelector('#k-creams .k-sw');
  var firstTcolor = document.querySelector('#k-tcolors .k-sw');

  var MAX_IMG = 3, MAX_TEXT = 3;
  // Размеры считаются в долях диаметра торта. Базовый кегль надписи = 8.6% диаметра.
  // Пределы подобраны так, чтобы при стандартном превью (~430 px) минимум был
  // 100 px для картинки и 30 px для надписи.
  var LIM = {
    img:  { min: 0.23, max: 1.3 },
    text: { min: 0.81, max: 2.5 }
  };
  function clampSize(it, v) {
    var l = LIM[it.type === 'img' ? 'img' : 'text'];
    return Math.max(l.min, Math.min(l.max, v));
  }
  var state = {
    cream: firstCream ? firstCream.dataset.color : '#FDFBF7',
    creamName: firstCream ? firstCream.dataset.name : '',
    tcolor: firstTcolor ? firstTcolor.dataset.color : '#E0527F',
    tcolorName: firstTcolor ? firstTcolor.dataset.name : '',
    items: [],
    sel: null,
    seq: 0
  };

  // Всплывающее уведомление
  var toastEl = document.getElementById('k-toast');
  var toastTimer = null;
  function toast(msg) {
    toastEl.textContent = msg;
    toastEl.hidden = false;
    toastEl.classList.add('on');
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      toastEl.classList.remove('on');
      setTimeout(function () { toastEl.hidden = true; }, 300);
    }, 3200);
  }

  function itemsOf(type) {
    return state.items.filter(function (i) { return i.type === type; });
  }
  function byId(id) {
    for (var i = 0; i < state.items.length; i++) if (state.items[i].id === id) return state.items[i];
    return null;
  }
  function selItem() { return state.sel ? byId(state.sel) : null; }

  // ---- Рендер торта ----
  function render() {
    cake.style.setProperty('--cream', state.cream);
    var d = cake.clientWidth || 400;
    // удаляем узлы исчезнувших элементов
    [].slice.call(cake.children).forEach(function (node) {
      if (!byId(node.dataset.id)) node.remove();
    });
    state.items.forEach(function (it, idx) {
      var el = cake.querySelector('[data-id="' + it.id + '"]');
      if (!el) {
        el = it.type === 'img' ? new Image() : document.createElement('div');
        el.className = 'k-el ' + (it.type === 'img' ? 'k-el-img' : 'k-el-text');
        el.dataset.id = it.id;
        if (it.type === 'img') { el.draggable = false; el.alt = ''; }
        cake.appendChild(el);
        makeDraggable(el, it.id);
      }
      el.style.zIndex = idx + 1;
      el.classList.toggle('sel', state.sel === it.id);
      if (it.type === 'img') {
        if (el.getAttribute('src') !== it.src) el.src = it.src;
        var s = it.size * d;
        el.style.width = s + 'px';
        el.style.left = (d / 2 + it.x * d / 2) + 'px';
        el.style.top = (d / 2 + it.y * d / 2) + 'px';
      } else {
        el.textContent = it.text;
        el.style.color = it.color;
        el.style.fontSize = (d * 0.086 * it.size) + 'px';
        el.style.left = (d / 2 + it.x * d / 2) + 'px';
        el.style.top = (d / 2 + it.y * d / 2) + 'px';
      }
    });
    // панель выбранного
    var sel = selItem();
    toolsOn.hidden = !sel;
    toolsEmpty.hidden = !!sel;
    if (sel) {
      var l = LIM[sel.type === 'img' ? 'img' : 'text'];
      sizeRange.min = Math.round(l.min * 100);
      sizeRange.max = Math.round(l.max * 100);
      sizeRange.value = Math.round(sel.size * 100);
    }
    // кнопка добавления надписи
    addTextBtn.hidden = itemsOf('text').length >= MAX_TEXT;
  }

  // ---- Перетаскивание ----
  function makeDraggable(el, id) {
    var drag = null;
    el.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      state.sel = id;
      render();
      syncTextRows();
      el.setPointerCapture(e.pointerId);
      var it = byId(id);
      drag = { px: e.clientX, py: e.clientY, x: it.x, y: it.y };
    });
    el.addEventListener('pointermove', function (e) {
      if (!drag) return;
      var it = byId(id);
      if (!it) return;
      var half = cake.clientWidth / 2;
      it.x = Math.max(-0.92, Math.min(0.92, drag.x + (e.clientX - drag.px) / half));
      it.y = Math.max(-0.92, Math.min(0.92, drag.y + (e.clientY - drag.py) / half));
      render();
    });
    function stop() { drag = null; }
    el.addEventListener('pointerup', stop);
    el.addEventListener('pointercancel', stop);
  }
  cake.addEventListener('pointerdown', function (e) {
    if (e.target === cake) { state.sel = null; render(); }
  });

  // ---- Картинки ----
  function addImage(src) {
    if (itemsOf('img').length >= MAX_IMG) { toast(PROD_CFG.limitImg); return null; }
    var id = 'i' + (++state.seq);
    var n = itemsOf('img').length;
    state.items.push({
      id: id, type: 'img', src: src,
      x: n === 0 ? 0 : (n % 2 ? -0.3 : 0.3),
      y: n === 0 ? -0.15 : (n < 2 ? -0.15 : 0.2),
      size: 0.55
    });
    state.sel = id;
    render();
    return id;
  }
  // Загрузка клиентского фото на сервер — ссылка уйдёт в заказ отдельной строкой
  function uploadFile(file) {
    var fd = new FormData();
    fd.append('photo', file);
    fd.append('csrf', PROD_CFG.csrf);
    return fetch(PROD_CFG.upload, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.ok && res.url) {
          return res.url.indexOf('http') === 0 ? res.url : location.origin + res.url;
        }
        return null;
      })
      .catch(function () { return null; });
  }
  document.getElementById('k-stickers').addEventListener('click', function (e) {
    var b = e.target.closest('.k-st');
    if (!b) return;
    if (b.id === 'k-own') { fileInp.click(); return; }
    if (!b.dataset.src) { // «Очистить всё»
      state.items = itemsOf('text');
      state.sel = null;
      render();
      return;
    }
    addImage(b.dataset.src);
  });
  fileInp.addEventListener('change', function () {
    var f = fileInp.files && fileInp.files[0];
    fileInp.value = '';
    if (!f) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1) { toast(PROD_CFG.errors.type); return; }
    if (f.size > 8 * 1024 * 1024) { toast(PROD_CFG.errors.size); return; }
    var id = addImage(URL.createObjectURL(f));
    if (!id) return;
    var it = byId(id);
    it.own = true;
    it.uploading = uploadFile(f).then(function (url) {
      it.url = url;
      if (!url) toast(PROD_CFG.errors.generic);
      return url;
    });
  });

  // ---- Надписи ----
  function addText(initial, silent) {
    if (itemsOf('text').length >= MAX_TEXT) { if (!silent) toast(PROD_CFG.limitText); return null; }
    var id = 't' + (++state.seq);
    var n = itemsOf('text').length;
    state.items.push({
      id: id, type: 'text', text: initial || '',
      color: state.tcolor, colorName: state.tcolorName,
      x: 0, y: n === 0 ? 0.62 : 0.62 - 0.22 * n, size: 1.1
    });
    return id;
  }
  function syncTextRows() {
    var texts = itemsOf('text');
    textsBox.innerHTML = '';
    texts.forEach(function (it, i) {
      var row = document.createElement('div');
      row.className = 'k-text-row' + (state.sel === it.id ? ' sel' : '');
      var inp = document.createElement('input');
      inp.className = 'txt';
      inp.type = 'text';
      inp.maxLength = 40;
      inp.value = it.text;
      inp.placeholder = PROD_CFG.textPh.replace('%d', i + 1);
      inp.addEventListener('input', function () {
        it.text = inp.value.replace(/[\u0000-\u001F\u007F]/g, ' ');
        render();
      });
      inp.addEventListener('focus', function () { state.sel = it.id; render(); markRows(); });
      var x = document.createElement('button');
      x.type = 'button';
      x.className = 'k-row-x';
      x.setAttribute('aria-label', PROD_CFG.delLbl);
      x.textContent = '×';
      x.addEventListener('click', function () {
        state.items = state.items.filter(function (o) { return o.id !== it.id; });
        if (state.sel === it.id) state.sel = null;
        render();
        syncTextRows();
      });
      row.appendChild(inp);
      row.appendChild(x);
      row.dataset.id = it.id;
      textsBox.appendChild(row);
    });
    render();
  }
  function markRows() {
    [].slice.call(textsBox.children).forEach(function (r) {
      r.classList.toggle('sel', r.dataset.id === state.sel);
    });
  }
  addTextBtn.addEventListener('click', function () {
    var id = addText('');
    if (!id) return;
    state.sel = id;
    syncTextRows();
    var last = textsBox.lastElementChild;
    if (last) last.querySelector('input').focus();
  });

  // ---- Свотчи ----
  function swatchGroup(id, cb) {
    var box = document.getElementById(id);
    box.addEventListener('click', function (e) {
      var b = e.target.closest('.k-sw');
      if (!b) return;
      box.querySelectorAll('.k-sw').forEach(function (x) { x.classList.toggle('on', x === b); });
      cb(b.dataset.color, b.dataset.name);
    });
  }
  swatchGroup('k-creams', function (c, n) { state.cream = c; state.creamName = n; render(); });
  swatchGroup('k-tcolors', function (c, n) {
    state.tcolor = c; state.tcolorName = n;
    var sel = selItem();
    var targets = (sel && sel.type === 'text') ? [sel] : itemsOf('text');
    targets.forEach(function (it) { it.color = c; it.colorName = n; });
    render();
  });

  // ---- Размер и удаление ----
  sizeRange.addEventListener('input', function () {
    var sel = selItem();
    if (!sel) return;
    sel.size = clampSize(sel, sizeRange.value / 100);
    render();
  });
  delBtn.addEventListener('click', function () {
    var sel = selItem();
    if (!sel) return;
    state.items = state.items.filter(function (o) { return o.id !== sel.id; });
    state.sel = null;
    render();
    syncTextRows();
  });
  window.addEventListener('resize', render);

  // первая пустая надпись
  addText('');
  syncTextRows();

  // ---- Рендер макета в canvas -> загрузка -> ссылка в WhatsApp ----
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
      await document.fonts.load('700 80px Caveat');
      var S = 900, R = 400, cx = S / 2, cy = S / 2;
      var cv = document.createElement('canvas');
      cv.width = S; cv.height = S;
      var g = cv.getContext('2d');
      g.fillStyle = '#F3F0EB';
      g.fillRect(0, 0, S, S);
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
      g.strokeStyle = 'rgba(0,0,0,0.06)';
      g.lineWidth = 10;
      g.beginPath(); g.arc(cx, cy, R - 6, 0, 7); g.stroke();

      for (var k = 0; k < state.items.length; k++) {
        var it = state.items[k];
        if (it.type === 'img') {
          var im = await loadImg(it.src);
          var s = it.size * 2 * R;
          var ratio = im.naturalWidth / im.naturalHeight;
          var w = s, h = s;
          if (ratio > 1) h = s / ratio; else w = s * ratio;
          g.save();
          g.beginPath(); g.arc(cx, cy, R - 12, 0, 7); g.clip();
          g.drawImage(im, cx + it.x * R - w / 2, cy + it.y * R - h / 2, w, h);
          g.restore();
        } else if (it.text) {
          var fs = R * 0.172 * it.size; // 8.6% диаметра — как в превью
          g.font = '700 ' + fs + 'px Caveat, cursive';
          while (g.measureText(it.text).width > R * 1.6 && fs > 24) {
            fs -= 3;
            g.font = '700 ' + fs + 'px Caveat, cursive';
          }
          g.fillStyle = it.color;
          g.textAlign = 'center';
          g.textBaseline = 'middle';
          if (String(it.color).toLowerCase() === '#ffffff') {
            g.shadowColor = 'rgba(0,0,0,.35)'; g.shadowBlur = 6;
          }
          g.fillText(it.text, cx + it.x * R, cy + it.y * R);
          g.shadowBlur = 0;
        }
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

  function buildExtras() {
    var extras = [PROD_CFG.creamLbl + ': ' + state.creamName];
    itemsOf('text').forEach(function (it) {
      if (it.text.trim()) extras.push(PROD_CFG.labels.text + ': «' + it.text.trim() + '» (' + (it.colorName || state.tcolorName) + ')');
    });
    // ссылки на фото, которые загрузил клиент — отдельными строками
    var own = itemsOf('img').filter(function (it) { return it.own; });
    own.forEach(function (it, i) {
      if (it.url) extras.push(PROD_CFG.photoLbl + (own.length > 1 ? ' ' + (i + 1) : '') + ': ' + it.url);
    });
    if (window.__setExtras) window.__setExtras(extras);
  }
  // ссылки на фото клиента — уходят и в заказ, который сохраняется у нас
  window.__orderPhotos = function () {
    return itemsOf('img').filter(function (it) { return it.own && it.url; }).map(function (it) { return it.url; });
  };

  document.getElementById('prod-order').addEventListener('click', function () {
    buildExtras();
    var send = document.getElementById('modal-send');
    send.classList.add('busy');
    var pending = itemsOf('img').filter(function (it) { return it.own && it.uploading; }).map(function (it) { return it.uploading; });
    Promise.all(pending.concat([renderAndUpload()])).then(function () {
      buildExtras();
      send.classList.remove('busy');
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
