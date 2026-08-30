// Mobile menu
var burger = document.getElementById('burger'), menu = document.getElementById('menu');
if (burger && menu) {
  burger.addEventListener('click', function () {
    var open = menu.classList.toggle('open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  menu.addEventListener('click', function (e) {
    if (e.target.tagName === 'A') menu.classList.remove('open');
  });
}
// Product configurator (size / sponge / filling -> price + WhatsApp message)
(function () {
  var cfg = window.PROD_CFG;
  if (!cfg) return;
  var sizeSel = document.getElementById('opt-size');
  var spongeSel = document.getElementById('opt-sponge');
  var fillSel = document.getElementById('opt-fill');
  var priceEl = document.getElementById('prod-price');
  var orderEl = document.getElementById('prod-order');
  if (!sizeSel || !spongeSel || !fillSel || !priceEl || !orderEl) return;

  function refillFillings() {
    var items = cfg.sponges[spongeSel.selectedIndex][1];
    fillSel.innerHTML = items.map(function (i) {
      return '<option>' + i.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</option>';
    }).join('');
  }
  var designUrl = '';
  function update() {
    var size = cfg.sizes[sizeSel.selectedIndex];
    priceEl.textContent = size[1] + ' ₼';
    var msg = cfg.intro
      + ' ' + cfg.labels.size + ': ' + size[0] + '.'
      + ' ' + cfg.labels.sponge + ': ' + cfg.sponges[spongeSel.selectedIndex][0] + '.'
      + ' ' + cfg.labels.fill + ': ' + fillSel.value + '.';
    if (designUrl) msg += ' ' + cfg.design + ': ' + designUrl;
    orderEl.href = cfg.wa + encodeURIComponent(msg);
  }
  sizeSel.addEventListener('change', update);
  spongeSel.addEventListener('change', function () { refillFillings(); update(); });
  fillSel.addEventListener('change', update);
  update();

  // «Отправьте свой дизайн» — загрузка фото
  var upInput = document.getElementById('up-input');
  var upIdle = document.getElementById('up-idle');
  var upLoad = document.getElementById('up-load');
  var upDone = document.getElementById('up-done');
  var upErr = document.getElementById('up-err');
  var upThumb = document.getElementById('up-thumb');
  var upRemove = document.getElementById('up-remove');
  if (upInput && upIdle) {
    function upState(state, errText) {
      upIdle.hidden = state !== 'idle';
      upLoad.hidden = state !== 'load';
      upDone.hidden = state !== 'done';
      upErr.hidden = !errText;
      upErr.textContent = errText || '';
    }
    upIdle.addEventListener('click', function () { upInput.click(); });
    upRemove.addEventListener('click', function () {
      designUrl = '';
      upInput.value = '';
      upState('idle');
      update();
    });
    upInput.addEventListener('change', function () {
      var f = upInput.files && upInput.files[0];
      if (!f) return;
      if (['image/jpeg', 'image/png', 'image/webp'].indexOf(f.type) === -1) { upState('idle', cfg.errors.type); return; }
      if (f.size > 8 * 1024 * 1024) { upState('idle', cfg.errors.size); return; }
      upState('load');
      var fd = new FormData();
      fd.append('photo', f);
      fd.append('csrf', cfg.csrf);
      fetch(cfg.upload, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.ok && res.url) {
            designUrl = location.origin + res.url;
            upThumb.src = URL.createObjectURL(f);
            upState('done');
            update();
          } else {
            upState('idle', cfg.errors[(res && res.error) || 'generic'] || cfg.errors.generic);
          }
        })
        .catch(function () { upState('idle', cfg.errors.generic); });
    });
  }
})();
// Product page tabs
document.querySelectorAll('.tabs-wrap').forEach(function (wrap) {
  wrap.addEventListener('click', function (e) {
    var btn = e.target.closest('.tab-btn');
    if (!btn) return;
    var key = btn.getAttribute('data-tab');
    wrap.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.toggle('active', b === btn); });
    wrap.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-panel') === key); });
  });
});
// Reveal on scroll (content stays visible when IO is unavailable)
if ('IntersectionObserver' in window) {
  document.documentElement.classList.add('anim');
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
    });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
}
