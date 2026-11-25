// Новогодний снег: лёгкий холст поверх страницы, клики проходят насквозь.
// Не запускается, если в системе выключены анимации или на странице свой тяжёлый
// холст (конструктор с 3D); останавливается, пока вкладка скрыта.
(function () {
  if (!window.requestAnimationFrame || document.body.getAttribute('data-ny-snow') === '0') return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var cv = document.createElement('canvas');
  var g = cv.getContext && cv.getContext('2d');
  if (!g) return;
  cv.className = 'ny-snow';
  cv.setAttribute('aria-hidden', 'true');
  document.body.appendChild(cv);

  var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
  var W = 0, H = 0, flakes = [], raf = 0, last = 0;
  // половина хлопьев белые с голубым контуром, половина бледно-голубые — видны и на белом, и на голубом
  function flake(anywhere) {
    return {
      x: Math.random() * W,
      y: anywhere ? Math.random() * H : -6 - Math.random() * 40,
      r: 1.6 + Math.random() * 2.6,
      v: 0.35 + Math.random() * 0.75,          // скорость падения, px за кадр 60 Гц
      p: Math.random() * Math.PI * 2,          // фаза покачивания
      s: 0.3 + Math.random() * 0.7,            // размах покачивания
      w: Math.random() < 0.5,                  // белая (с голубым контуром) или голубая
      a: 0.6 + Math.random() * 0.35
    };
  }
  function size() {
    W = window.innerWidth; H = window.innerHeight;
    cv.width = Math.round(W * dpr); cv.height = Math.round(H * dpr);
    g.setTransform(dpr, 0, 0, dpr, 0, 0);
    // число хлопьев — по площади экрана: на телефоне ~15, на большом мониторе до 70
    var n = Math.round(Math.max(14, Math.min(70, W * H / 20000)));
    while (flakes.length < n) flakes.push(flake(true));
    flakes.length = n;
  }
  function frame(t) {
    raf = requestAnimationFrame(frame);
    if (!last) { last = t; return; }             // первый кадр — только засекаем время
    var dt = t - last;
    if (dt < 22) return;                         // ~45 кадров в секунду хватает, телефон не греется
    last = t;
    var k = Math.min(dt, 60) / 16.7;
    g.clearRect(0, 0, W, H);
    for (var i = 0; i < flakes.length; i++) {
      var f = flakes[i];
      f.p += 0.012 * k;
      f.y += f.v * k;
      f.x += Math.sin(f.p) * f.s * 0.35 * k;
      if (f.y > H + 6 || f.x < -10 || f.x > W + 10) { flakes[i] = flake(false); continue; }
      g.beginPath();
      g.arc(f.x, f.y, f.r, 0, 6.2832);
      if (f.w) {
        g.fillStyle = 'rgba(255,255,255,' + f.a + ')';
        g.fill();
        g.strokeStyle = 'rgba(150,186,222,' + (f.a * 0.7) + ')';   // контур — чтобы белую было видно и на белом фоне
        g.lineWidth = 0.9;
        g.stroke();
      } else {
        g.fillStyle = 'rgba(164,198,232,' + f.a + ')';
        g.fill();
      }
    }
  }
  function start() { if (!raf) { last = 0; raf = requestAnimationFrame(frame); } }
  function stop() { if (raf) { cancelAnimationFrame(raf); raf = 0; } }

  var rt = 0;
  window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(size, 150); });
  document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
  size();
  start();
})();
