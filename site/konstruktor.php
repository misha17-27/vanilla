<?php
$page = 'konstr';
require_once __DIR__ . '/includes/config.php';
$page_title = seo_title('konstruktor', $t['k_title']);
$page_meta  = seo_desc('konstruktor', $t['k_meta']);
schema_breadcrumbs([[$t['nav_konstr'], '/konstruktor/']]);
require __DIR__ . '/includes/header.php';
?>
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
      <a href="<?= e(u('/')) ?>"><?= e($t['breadcrumb_home']) ?></a>
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
          <div class="k-cake" id="k-cake" style="--cream:#FDFBF7"><svg class="k-edge-svg" id="k-edge-svg" viewBox="0 0 200 200" aria-hidden="true"></svg></div>
          <!-- 3D-вид: на компьютере накрывает это превью, на телефоне переезжает в блок «Края торта» -->
          <div class="k-3d" id="k-3d" hidden>
            <canvas class="k-3d-cv" role="img" aria-label="<?= e($t['k_3d_aria']) ?>"></canvas>
            <div class="k-3d-load" id="k-3d-load"><span class="up-spin"></span><?= e($t['k_3d_loading']) ?></div>
            <button type="button" class="k-3d-rot l" data-rot="-1" aria-label="<?= e($t['k_3d_left']) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 1 0 2.3-5.6"/><path d="M4 4v5h5"/></svg></button>
            <button type="button" class="k-3d-rot r" data-rot="1" aria-label="<?= e($t['k_3d_right']) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12a8 8 0 1 1-2.3-5.6"/><path d="M20 4v5h-5"/></svg></button>
            <div class="k-3d-hint"><?= e($t['k_3d_hint']) ?></div>
          </div>
          <div class="k-toast" id="k-toast" role="status" aria-live="polite" hidden></div>
          <div class="k-views" id="k-views">
            <button type="button" class="k-view on" data-view="top" aria-pressed="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="3"/></svg><?= e($t['k_view_top']) ?></button>
            <button type="button" class="k-view" data-view="3d" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><ellipse cx="12" cy="7.5" rx="8" ry="3"/><path d="M4 7.5v8c0 1.7 3.6 3 8 3s8-1.3 8-3v-8"/></svg><?= e($t['k_view_3d']) ?></button>
          </div>
        </div>
        <div class="k-tools" id="k-tools">
          <div class="k-tools-empty" id="k-tools-empty"><?= e($t['k_sel_none']) ?></div>
          <div class="k-tools-on" id="k-tools-on" hidden>
            <span class="k-lbl-s"><?= e($t['k_sel_size']) ?></span>
            <input type="range" id="k-size" min="20" max="130" value="55">
            <button type="button" class="k-del" id="k-del"><?= e($t['k_del']) ?></button>
          </div>
        </div>
        <p class="k-hint" id="k-hint"><?= e($t['k_hint']) ?></p>
      </div>

      <!-- Панель -->
      <div class="k-panel">
        <!-- Размер — первым: от него зависит, какие края можно сделать -->
        <div class="k-group">
          <div class="k-lbl" id="k-size-lbl"><?= e($t['k_size']) ?></div>
          <div class="k-sizes" id="k-sizes" role="group" aria-labelledby="k-size-lbl">
            <?php foreach ($sizeOpts as $i => $o):
              $sp = explode(' · ', $o[0], 2); ?>
            <button type="button" class="k-size<?= $i === 0 ? ' on' : '' ?>" data-i="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
              <b><?= e($sp[0]) ?></b>
              <?php if (isset($sp[1])): ?><small><?= e($sp[1]) ?></small><?php endif; ?>
              <span class="k-size-p"><?= e($o[1]) ?> ₼</span>
            </button>
            <?php endforeach; ?>
          </div>
          <!-- сам выбор живёт в обычном списке: из него берёт размер и цену форма заказа -->
          <select id="opt-size" hidden aria-hidden="true" tabindex="-1" autocomplete="off">
            <?php foreach ($sizeOpts as $i => $o): ?>
            <option value="<?= $i ?>"><?= e($o[0]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

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
          <div class="k-lbl"><?= e($t['k_edge']) ?></div>
          <div class="k-3d-slot" id="k-3d-slot" hidden></div>
          <div class="k-edges" id="k-edges">
            <?php foreach ($t['k_edges'] as $i => $e): ?>
            <button type="button" class="k-edge<?= $i === 0 ? ' on' : '' ?>" data-edge="<?= e($e[0]) ?>" data-name="<?= e($e[1]) ?>" data-desc="<?= e($e[2]) ?>" data-desc-small="<?= e($e[3] ?? $e[2]) ?>">
              <span class="k-edge-ico" data-ico="<?= e($e[0]) ?>"></span>
              <span class="k-edge-txt"><b><?= e($e[1]) ?></b><small><?= e($e[2]) ?></small></span>
            </button>
            <?php endforeach; ?>
          </div>
          <p class="k-edge-note" id="k-edge-note" hidden><?= e($t['k_edge_small']) ?></p>
          <div class="k-lbl" id="k-ecolor-lbl" style="margin-top:16px" hidden><?= e($t['k_edge_color']) ?></div>
          <div class="k-swatches" id="k-ecolors" hidden>
            <?php foreach ($t['k_colors'] as $i => $c): ?>
            <button type="button" class="k-sw<?= $i === 0 ? ' on' : '' ?>" style="--c:<?= e($c[1]) ?>" data-color="<?= e($c[1]) ?>" data-name="<?= e($c[0]) ?>" title="<?= e($c[0]) ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="k-group">
          <div class="prod-price" id="prod-price"><?= e($sizeOpts[0][1]) ?> ₼</div>
          <div class="prod-opts" style="margin-top:14px">
            <div class="opt-row">
              <label><?= e($t['opt_sponge']) ?> · <?= e($t['opt_fill']) ?></label>
              <div class="duo">
                <select id="opt-sponge" autocomplete="off">
                  <?php foreach ([1, 2, 3] as $i): ?>
                  <option value="<?= $i ?>"><?= e($t["fl{$i}_t"]) ?></option>
                  <?php endforeach; ?>
                </select>
                <select id="opt-fill" autocomplete="off">
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
    'purl'    => CANON_HOST . u('/konstruktor/'),   // ссылка в WhatsApp — на версию того же языка
    'source'  => 'constructor',
    'orderName' => $t['k_h'],
    'linkLbl' => $t['wa_link_lbl'],
    'upload'  => '/upload-design.php',
    'csrf'    => $_SESSION['csrf'],
    'closed'  => load_schedule()['closed'],
    'weekly'  => load_schedule()['weekly'],
    'design'  => $t['wa_design'],
    'errors'  => [
        'type'    => $t['up_e_type'],
        'size'    => $t['up_e_size'],
        'rate'    => $t['up_e_rate'],
        'generic' => $t['up_e_generic'],
    ],
    'creamLbl' => $t['k_cream_lbl'],
    'edgeLbl'  => $t['k_edge'],
    'cake3d'   => '/assets/cake3d.js?v=' . @filemtime(__DIR__ . '/assets/cake3d.js'),
    'three'    => '/assets/vendor/three/three.module.min.js?v=' . @filemtime(__DIR__ . '/assets/vendor/three/three.module.min.js'),
    'k3dNet'   => $t['k_3d_net'],
    // номера размеров «1–2 персоны»: на таком торте нижний бортик у основания не делаем
    'smallSizes' => [0],
    'boardText' => 'Vanilla Cake',
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

  // Края торта. Доли радиуса, чтобы одинаково считалось и в превью, и в макете.
  // n — сколько фигурок по кругу, w/h — половина ширины и высота купола,
  // r — радиус шарика, out — насколько центр фигурки отступает от края внутрь.
  var EDGES = {
    dots:   { n: 30, r: 0.098, out: 0.925 },
    shells: { n: 26, r: 0.105, out: 0.940 }
  };
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
    seq: 0,
    edge: 'none',          // край торта: none | dots | shells
    edgeName: '',
    ecolor: firstCream ? firstCream.dataset.color : '#FDFBF7',
    ecolorName: firstCream ? firstCream.dataset.name : ''
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

  // ---- Край торта в превью: кольцо фигурок из SVG ----
  var edgeSvg = document.getElementById('k-edge-svg');
  function edgeMarkup(kind, color, R) {
    var cfg = EDGES[kind];
    if (!cfg) return '';
    var out = '';
    for (var i = 0; i < cfg.n; i++) {
      var deg = i * 360 / cfg.n;
      var tr = 'translate(100,100) rotate(' + deg.toFixed(2) + ') translate(0,' + (-cfg.out * R).toFixed(2) + ')';
      out += '<circle r="' + (cfg.r * R).toFixed(2) + '" transform="' + tr + '" fill="' + color + '"/>';
    }
    return out;
  }
  function renderEdge() {
    edgeSvg.innerHTML = edgeMarkup(state.edge, state.ecolor, 100);
    edgeSvg.hidden = state.edge === 'none';
  }

  // ---- Рендер торта ----
  function render() {
    cake.style.setProperty('--cream', state.cream);
    renderEdge();
    var d = cake.clientWidth || 400;
    // удаляем узлы исчезнувших элементов
    [].slice.call(cake.children).forEach(function (node) {
      // у слоя краёв нет data-id — его чистка не трогает
      if (node.dataset.id && !byId(node.dataset.id)) node.remove();
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
        el.style.fontSize = (d * 0.064 * it.size) + 'px';
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
    schedule3d();
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
    editStage();
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
      inp.addEventListener('focus', function () { editStage(); state.sel = it.id; render(); markRows(); });
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
    editStage();
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

  // Края торта: выбор формы и цвета. Цвет показываем только когда край выбран.
  var edgesBox = document.getElementById('k-edges');
  var ecolorBox = document.getElementById('k-ecolors');
  var ecolorLbl = document.getElementById('k-ecolor-lbl');
  edgesBox.addEventListener('click', function (e) {
    var b = e.target.closest('.k-edge');
    if (!b) return;
    edgesBox.querySelectorAll('.k-edge').forEach(function (x) { x.classList.toggle('on', x === b); });
    state.edge = b.dataset.edge;
    state.edgeName = b.dataset.name;
    var show = state.edge !== 'none';
    ecolorBox.hidden = !show;
    ecolorLbl.hidden = !show;
    render();
    buildExtras();
    edgeStage();
  });
  swatchGroup('k-ecolors', function (c, n) {
    state.ecolor = c; state.ecolorName = n;
    render();
    buildExtras();
    edgeStage();
  });
  // Картинки в кнопках выбора края — те же фигурки, что и на торте
  edgesBox.querySelectorAll('.k-edge-ico').forEach(function (ico) {
    var kind = ico.dataset.ico;
    if (kind === 'none') { ico.innerHTML = '<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="86" fill="none" stroke="currentColor" stroke-width="7" stroke-dasharray="10 9"/></svg>'; return; }
    // 92 вместо 100: шарики свисают за край торта и обрезались бы рамкой значка
    ico.innerHTML = '<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="70" fill="rgba(0,0,0,.05)"/>' + edgeMarkup(kind, 'currentColor', 92) + '</svg>';
  });
  // ---- Размер ----
  // Кнопки наверху управляют обычным списком #opt-size: из него форма заказа
  // берёт размер и цену. У маленького торта (1–2 персоны) край только сверху.
  var sizeSel = document.getElementById('opt-size');
  var sizesBox = document.getElementById('k-sizes');
  var edgeNote = document.getElementById('k-edge-note');
  function smallCake() {
    return (PROD_CFG.smallSizes || []).indexOf(sizeSel.selectedIndex) > -1;
  }
  function applySize() {
    var small = smallCake();
    edgesBox.querySelectorAll('.k-edge').forEach(function (b) {
      b.querySelector('small').textContent = small ? b.dataset.descSmall : b.dataset.desc;
    });
    edgeNote.hidden = !small;
    schedule3d();
  }
  function markSize() {
    sizesBox.querySelectorAll('.k-size').forEach(function (x) {
      var on = +x.dataset.i === sizeSel.selectedIndex;
      x.classList.toggle('on', on);
      x.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }
  sizesBox.addEventListener('click', function (e) {
    var b = e.target.closest('.k-size');
    if (!b) return;
    sizeSel.selectedIndex = +b.dataset.i;
    sizeSel.dispatchEvent(new Event('change'));   // цена и текст заказа
    markSize();
    applySize();
  });
  // при возврате «Назад» браузер может вернуть прежний размер в список — подсветку берём из него
  window.addEventListener('pageshow', function () { markSize(); applySize(); });
  markSize();
  applySize();

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

  // ---- Верх торта на canvas: для макета в WhatsApp и для текстуры 3D ----
  // flat — без фона, тени и бликов (в 3D объём даёт свет), ring — рисовать край
  // кольцом (в 3D он объёмный, отдельными фигурками). getImg(src) — готовая
  // картинка или null, если она ещё не загрузилась.
  function drawTop(g, S, R, getImg, o) {
    var cx = S / 2, cy = S / 2;
    if (o.flat) {
      g.fillStyle = state.cream;
      g.fillRect(0, 0, S, S);
    } else {
      g.fillStyle = '#F3F0EB';
      g.fillRect(0, 0, S, S);
      var grad = g.createRadialGradient(cx - R * 0.3, cy - R * 0.35, R * 0.2, cx, cy, R * 1.05);
      grad.addColorStop(0, 'rgba(255,255,255,0.55)');
      grad.addColorStop(0.55, 'rgba(255,255,255,0)');
      grad.addColorStop(1, 'rgba(0,0,0,0.10)');
      g.save();
      g.shadowColor = 'rgba(60,45,35,.25)';
      g.shadowBlur = R * 0.1;
      g.shadowOffsetY = R * 0.045;
      g.fillStyle = state.cream;
      g.beginPath(); g.arc(cx, cy, R, 0, 7); g.fill();
      g.restore();
      g.fillStyle = grad;
      g.beginPath(); g.arc(cx, cy, R, 0, 7); g.fill();
      g.strokeStyle = 'rgba(0,0,0,0.06)';
      g.lineWidth = R * 0.025;
      g.beginPath(); g.arc(cx, cy, R * 0.985, 0, 7); g.stroke();
    }

    // край торта — теми же пропорциями, что и в превью
    var ec = o.ring ? EDGES[state.edge] : null;
    if (ec) {
      // обрезаем по кругу торта — в превью это делает круглая маска,
      // иначе основания фигурок торчат наружу и силуэт становится гранёным
      g.save();
      g.beginPath(); g.arc(cx, cy, R, 0, 7); g.clip();
      g.fillStyle = state.ecolor;
      // лёгкая тень: без неё край в цвет торта сливается с покрытием
      g.shadowColor = 'rgba(0,0,0,.18)';
      g.shadowBlur = R * 0.015;
      g.shadowOffsetY = R * 0.005;
      for (var e = 0; e < ec.n; e++) {
        g.save();
        g.translate(cx, cy);
        g.rotate(e * 2 * Math.PI / ec.n);
        g.translate(0, -ec.out * R);
        g.beginPath();
        g.arc(0, 0, ec.r * R, 0, 7);
        g.fill();
        g.restore();
      }
      g.restore();
    }

    for (var k = 0; k < state.items.length; k++) {
      var it = state.items[k];
      if (it.type === 'img') {
        var im = getImg(it.src);
        if (!im) continue;
        var s = it.size * 2 * R;
        var ratio = (im.naturalWidth || im.width) / (im.naturalHeight || im.height);
        var w = s, h = s;
        if (ratio > 1) h = s / ratio; else w = s * ratio;
        g.save();
        g.beginPath(); g.arc(cx, cy, R * 0.97, 0, 7); g.clip();
        g.drawImage(im, cx + it.x * R - w / 2, cy + it.y * R - h / 2, w, h);
        g.restore();
      } else if (it.text) {
        var fs = R * 0.128 * it.size; // 6.4% диаметра — как в превью
        g.font = '800 ' + fs + 'px Montserrat, sans-serif';
        while (g.measureText(it.text).width > R * 1.6 && fs > R * 0.045) {
          fs -= R * 0.0075;
          g.font = '800 ' + fs + 'px Montserrat, sans-serif';
        }
        g.save();
        g.fillStyle = it.color;
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        if (String(it.color).toLowerCase() === '#ffffff') {
          g.shadowColor = 'rgba(0,0,0,.35)'; g.shadowBlur = R * 0.015;
        }
        g.fillText(it.text, cx + it.x * R, cy + it.y * R);
        g.restore();
      }
    }
  }

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
      await document.fonts.load('800 80px Montserrat');
      var imgs = {};
      for (var k = 0; k < state.items.length; k++) {
        var it = state.items[k];
        if (it.type === 'img' && !imgs[it.src]) imgs[it.src] = await loadImg(it.src);
      }
      var S = 900;
      var cv = document.createElement('canvas');
      cv.width = S; cv.height = S;
      drawTop(cv.getContext('2d'), S, 400, function (src) { return imgs[src] || null; }, { flat: false, ring: true });
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

  // ---- 3D-вид ----
  // Включается, когда переходят к выбору краёв: на виде сверху не видно, как край
  // ложится по бокам. Для правки картинок и надписей возвращаемся к виду сверху —
  // перетаскивать элементы можно только там.
  // На компьютере 3D накрывает превью слева (оно прилипает к экрану), на телефоне
  // превью далеко вверху, поэтому сцена переезжает прямо в блок «Края торта».
  var k3 = { host: 'none', api: null, mod: null, ready: null, failed: false, raf: 0, tries: 0, vis: true, dirty: false };
  var stage = document.getElementById('k-3d');
  var stageCv = stage.querySelector('canvas');
  var stageLoad = document.getElementById('k-3d-load');
  var slotEdges = document.getElementById('k-3d-slot');
  var box = cake.parentNode;
  var viewsBox = document.getElementById('k-views');
  var hintEl = document.getElementById('k-hint');
  var toolsBox = document.getElementById('k-tools');
  var wideMq = window.matchMedia('(min-width: 1061px)');
  var topCv = document.createElement('canvas');
  topCv.width = topCv.height = 1024;

  // import() — синтаксис, который старые браузеры не понимают; через Function
  // ошибка случится только при вызове, а не сломает весь конструктор
  var dynImport = null;
  try { dynImport = new Function('u', 'return import(u)'); } catch (err) { dynImport = null; }
  if (!dynImport) viewsBox.hidden = true;

  // three.js нужен WebGL2. Проверяем до загрузки: без него 690 КБ качать незачем,
  // а кнопку «Объём» прячем — края и так видны на плоском превью.
  var gl2 = null;
  function hasGL2() {
    if (gl2 === null) {
      try {
        var gl = document.createElement('canvas').getContext('webgl2');
        gl2 = !!gl;
        var x = gl && gl.getExtension('WEBGL_lose_context');
        if (x) x.loseContext();   // пробный контекст сразу отпускаем
      } catch (err) { gl2 = false; }
    }
    return gl2;
  }

  // Модуль сцены и three грузим вместе. Неудачную загрузку браузер запоминает
  // по адресу, поэтому повторная попытка идёт с новой меткой в адресе.
  function load3d() {
    if (!dynImport) return Promise.reject(new Error('no-import'));
    if (!hasGL2()) {
      k3.failed = true;
      viewsBox.hidden = true;
      return Promise.reject(new Error('no-webgl'));
    }
    if (!k3.mod) {
      var r = k3.tries++ ? '&r=' + k3.tries : '';
      k3.mod = Promise.all([dynImport(PROD_CFG.three + r), dynImport(PROD_CFG.cake3d + r)])
        .then(function (a) { a[1].init(a[0]); return a[1]; })
        .catch(function (err) {
          k3.mod = null;
          var e = new Error('net');
          e.net = true;
          throw e;
        });
    }
    return k3.mod;
  }

  // Картинки для текстуры: берём из кэша, недогруженные дорисуем по готовности.
  // Большое фото с телефона сразу уменьшаем — иначе каждый кадр масштабировал бы 12 Мп.
  var imgCache = {};
  function cachedImg(src) {
    var c = imgCache[src];
    if (!c) {
      c = imgCache[src] = { im: null };
      var im = new Image();
      im.onload = function () {
        var k = Math.min(1, 1024 / Math.max(im.naturalWidth, im.naturalHeight));
        if (k < 1) {
          var cv = document.createElement('canvas');
          cv.width = Math.round(im.naturalWidth * k);
          cv.height = Math.round(im.naturalHeight * k);
          cv.getContext('2d').drawImage(im, 0, 0, cv.width, cv.height);
          c.im = cv;
        } else {
          c.im = im;
        }
        schedule3d();
      };
      im.src = src;
    }
    return c.im;
  }

  // Пока сцена за пределами экрана (на телефоне она в блоке краёв, а правят торт
  // наверху), ничего не перерисовываем — только помечаем, что пора обновить.
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (en) {
      k3.vis = en[en.length - 1].isIntersecting;
      if (k3.vis && k3.dirty) schedule3d();
    }).observe(stage);
  }

  function sync3d() {
    k3.raf = 0;
    k3.dirty = false;
    if (!k3.api || k3.host === 'none') return;
    drawTop(topCv.getContext('2d'), 1024, 512, cachedImg, { flat: true, ring: false });
    k3.api.setCream(state.cream);
    k3.api.setBox(smallCake());   // 1–2 персоны — в бенто-боксе
    k3.api.setEdge(state.edge, state.ecolor, !smallCake());
    k3.api.topChanged();
  }
  // render() срабатывает и до этого блока (первая надпись), тогда k3 ещё нет
  function schedule3d() {
    if (!k3 || !k3.api || k3.host === 'none') return;
    if (!k3.vis) { k3.dirty = true; return; }
    if (!k3.raf) k3.raf = requestAnimationFrame(sync3d);
  }

  // Сбой: без WebGL2 тихо остаёмся на виде сверху. Если не загрузилось по сети —
  // говорим об этом, и следующий выбор края попробует ещё раз.
  function fail3d(err) {
    var net = err && err.net;
    if (!net) {
      k3.failed = true;
      viewsBox.hidden = true;
    }
    k3.ready = null;
    var wasShown = k3.host !== 'none';
    mount('none');
    if (net && wasShown) toast(PROD_CFG.k3dNet);
  }

  function ensure3d() {
    if (k3.failed) return Promise.reject(new Error('3d-failed'));
    if (!k3.ready) {
      k3.ready = load3d().then(function (m) {
        k3.api = m.createCake3D(stageCv, {
          topCanvas: topCv,
          boardText: PROD_CFG.boardText
        });
        stageLoad.hidden = true;
        document.fonts.load('800 80px Montserrat').then(schedule3d).catch(function () {});
        sync3d();
        k3.api.intro();
      });
      k3.ready.catch(fail3d);
    }
    return k3.ready;
  }

  function setViewButtons(v) {
    viewsBox.querySelectorAll('.k-view').forEach(function (b) {
      var on = b.dataset.view === v;
      b.classList.toggle('on', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  // host: none — только вид сверху; preview — 3D вместо превью; edges — 3D в блоке краёв
  function mount(host) {
    if (host !== 'none' && k3.failed) host = 'none';
    var slotWasHidden = slotEdges.hidden;
    k3.host = host;
    if (host === 'preview') box.appendChild(stage);
    else if (host === 'edges') slotEdges.appendChild(stage);
    stage.hidden = host === 'none';
    slotEdges.hidden = host !== 'edges';
    box.classList.toggle('is-3d', host === 'preview');
    toolsBox.hidden = host === 'preview';
    hintEl.hidden = host === 'preview';
    setViewButtons(host === 'preview' ? '3d' : 'top');
    if (host === 'none') return;
    if (host === 'edges' && slotWasHidden) {
      requestAnimationFrame(function () {
        var r = stage.getBoundingClientRect();
        // снизу экран закрывает зелёная кнопка «Заказать в WhatsApp» (на узких экранах)
        var cta = document.querySelector('.mob-cta');
        var bottom = cta && getComputedStyle(cta).display !== 'none' ? cta.getBoundingClientRect().top : window.innerHeight;
        // 80 — высота прилипшей шапки сайта с запасом
        if (r.top < 80) stage.scrollIntoView({ block: 'start', behavior: 'smooth' });
        else if (r.bottom > bottom) stage.scrollIntoView({ block: 'end', behavior: 'smooth' });
      });
    }
    ensure3d().then(function () {
      if (k3.host === 'none') return;
      k3.api.resize();
      sync3d();
    }).catch(function () {});
  }

  // этап выбора краёв -> объём
  function edgeStage() {
    if (k3.failed || !dynImport || !hasGL2()) return;
    var host = wideMq.matches ? 'preview' : 'edges';
    if (k3.host !== host) mount(host);
  }
  // правка картинок и надписей -> вид сверху
  function editStage() {
    if (k3.host === 'preview') mount('none');
  }

  viewsBox.addEventListener('click', function (e) {
    var b = e.target.closest('.k-view');
    if (!b) return;
    if (b.dataset.view === '3d') mount('preview');
    else if (k3.host === 'preview') mount('none');
  });
  // сцену подгружаем заранее: при наведении на «Объём» и когда блок краёв на подходе
  viewsBox.addEventListener('pointerenter', function () { load3d().catch(function () {}); });
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (en) {
      if (en.some(function (x) { return x.isIntersecting; })) {
        load3d().catch(function () {});
        io.disconnect();
      }
    }, { rootMargin: '300px' });
    io.observe(edgesBox);
  }
  stage.addEventListener('click', function (e) {
    var b = e.target.closest('.k-3d-rot');
    if (b && k3.api) k3.api.rotateStep(+b.dataset.rot);
  });
  // подсказку «потяните» убираем, как только торт повернули
  stageCv.addEventListener('pointerdown', function () { stage.classList.add('used'); });
  function onWide() {
    if (k3.host === 'edges' && wideMq.matches) mount('preview');
  }
  if (wideMq.addEventListener) wideMq.addEventListener('change', onWide);
  else if (wideMq.addListener) wideMq.addListener(onWide);

  function buildExtras() {
    var extras = [PROD_CFG.creamLbl + ': ' + state.creamName];
    if (state.edge !== 'none') extras.push(PROD_CFG.edgeLbl + ': ' + state.edgeName + ' (' + state.ecolorName + ')');
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
