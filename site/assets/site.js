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
  function update() {
    var size = cfg.sizes[sizeSel.selectedIndex];
    priceEl.textContent = size[1] + ' ₼';
    var msg = cfg.intro
      + ' ' + cfg.labels.size + ': ' + size[0] + '.'
      + ' ' + cfg.labels.sponge + ': ' + cfg.sponges[spongeSel.selectedIndex][0] + '.'
      + ' ' + cfg.labels.fill + ': ' + fillSel.value + '.';
    orderEl.href = cfg.wa + encodeURIComponent(msg);
  }
  sizeSel.addEventListener('change', update);
  spongeSel.addEventListener('change', function () { refillFillings(); update(); });
  fillSel.addEventListener('change', update);
  update();
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
