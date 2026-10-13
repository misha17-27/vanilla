// 3D-вид торта для конструктора: корж с покрытием, рисунок сверху (та же картинка,
// что и в плоском превью), края из крема и подложка с надписью.
// Кадр рисуем по запросу, а не в бесконечном цикле: пока торт не крутят,
// страница не нагружает телефон.
//
// Three.js конструктор подгружает сам и передаёт через init(): так у обоих файлов
// версия в адресе, и после сбоя сети повторная попытка идёт по новому адресу —
// браузер запоминает неудачную загрузку модуля и по старому адресу не повторяет.
let THREE = null;
export function init(three) { THREE = three; }

const R = 1;           // радиус торта
const H = 1.1;         // высота: бенто невысокие, примерно половина диаметра
const BEVEL = 0.05;    // скругление верхней кромки — у крема не бывает острого угла
const BOARD_R = 1.42;  // подложка
const BOARD_H = 0.07;
const FIT = 1.48;      // радиус сферы, которая должна поместиться в кадр

// Края. Число фигурок совпадает с плоским превью, чтобы оба вида показывали одно и то же.
// Шарики стоят вплотную и чуть свисают за край, как у кондитеров.
// overlap — насколько соседние ракушки заходят друг на друга (хвост под голову следующей).
const EDGES = {
  dots:   { n: 30, shape: 'ball',  size: 0.098, rho: 0.925, top: true, bottom: false },
  shells: { n: 26, shape: 'shell', overlap: 1.35, top: true, bottom: true },
};
const SHELL_LEN = 2.75;   // длина заготовки ракушки до масштабирования (см. shellGeometry)

// После computeVertexNormals у сферы и тела вращения остаётся шов: вершины на нём
// продублированы, и нормали у дублей разные. Усредняем их по совпадающим позициям.
// Ключ — целые десятитысячные: у toFixed «-0.0000» и «0.0000» разные строки.
function smoothSeams(geo) {
  geo.computeVertexNormals();
  const p = geo.attributes.position, n = geo.attributes.normal;
  const acc = new Map();
  const q = (v) => String(Math.round(v * 1e4));
  const key = (i) => q(p.getX(i)) + ',' + q(p.getY(i)) + ',' + q(p.getZ(i));
  for (let i = 0; i < p.count; i++) {
    const k = key(i);
    const a = acc.get(k) || [0, 0, 0];
    a[0] += n.getX(i); a[1] += n.getY(i); a[2] += n.getZ(i);
    acc.set(k, a);
  }
  for (let i = 0; i < p.count; i++) {
    const a = acc.get(key(i));
    const l = Math.hypot(a[0], a[1], a[2]) || 1;
    n.setXYZ(i, a[0] / l, a[1] / l, a[2] / l);
  }
  n.needsUpdate = true;
  return geo;
}

// Шарик крема: круглый и пухлый, снизу приплюснут — лежит на торте, а не стоит
// на точке. Сверху едва заметный хвостик, который остаётся от кондитерского мешка.
// Радиус 1, основание на y = 0.
function ballGeometry() {
  const geo = new THREE.SphereGeometry(1, 40, 24);
  const p = geo.attributes.position;
  for (let i = 0; i < p.count; i++) {
    const x = p.getX(i), z = p.getZ(i);
    let y = p.getY(i);
    if (y < 0) y *= 0.42;
    else y = y * 0.94 + 0.07 * Math.max(0, 1 - (x * x + z * z) / 0.05);
    p.setXYZ(i, x, y + 0.42, z);
  }
  return smoothSeams(geo);
}

// Ракушка: круглая голова, вытянутый сужающийся хвост, гребни по кругу.
// Ось x — вдоль ракушки (голова в минус, хвост в плюс), y — вверх, z — поперёк.
function shellGeometry() {
  const geo = new THREE.SphereGeometry(1, 56, 28);
  const p = geo.attributes.position;
  for (let i = 0; i < p.count; i++) {
    let x = p.getX(i), y = p.getY(i), z = p.getZ(i);
    const taper = 1 - 0.78 * Math.max(0, x);
    const ridge = 1 + 0.08 * Math.cos(11 * Math.atan2(y, z));
    y *= taper * ridge;
    z *= taper * ridge;
    if (y < 0) y *= 0.15;            // плоское основание лежит на торте
    x = x < 0 ? x * 0.95 : x * 1.8;  // общая длина 0.95 + 1.8 = SHELL_LEN
    p.setXYZ(i, x, y * 0.9, z);
  }
  return smoothSeams(geo);
}

// Боковина коржа со скруглённой верхней кромкой
function bodyGeometry() {
  const pts = [new THREE.Vector2(R, 0), new THREE.Vector2(R, H - BEVEL)];
  for (let i = 1; i <= 10; i++) {
    const a = (i / 10) * Math.PI / 2;
    pts.push(new THREE.Vector2(R - BEVEL + BEVEL * Math.cos(a), H - BEVEL + BEVEL * Math.sin(a)));
  }
  return smoothSeams(new THREE.LatheGeometry(pts, 128));
}

// Подложка: светлый картон, по переднему краю — название кондитерской
function boardCanvas(text) {
  const S = 1024;
  const cv = document.createElement('canvas');
  cv.width = cv.height = S;
  const g = cv.getContext('2d');
  const draw = () => {
    g.fillStyle = '#F7F4EF';
    g.fillRect(0, 0, S, S);
    if (!text) return;
    const cx = S / 2, cy = S / 2;
    const rr = (S / 2) * 0.86;   // посередине между тортом (0,70) и краем подложки (1,0)
    g.fillStyle = '#E2557F';
    g.font = 'italic 700 64px "Cormorant Garamond", Georgia, serif';
    g.textAlign = 'center';
    g.textBaseline = 'middle';
    // текст по нижней дуге: это передний край подложки. Верх букв смотрит к центру,
    // поэтому с точки зрения покупателя надпись читается прямо.
    const chars = Array.from(text);
    const widths = chars.map((ch) => g.measureText(ch).width + 2);
    const span = widths.reduce((s, w) => s + w, 0) / rr;
    let a = Math.PI / 2 + span / 2;
    chars.forEach((ch, i) => {
      const ac = a - widths[i] / 2 / rr;
      g.save();
      g.translate(cx + rr * Math.cos(ac), cy + rr * Math.sin(ac));
      g.rotate(ac - Math.PI / 2);
      g.fillText(ch, 0, 0);
      g.restore();
      a -= widths[i] / rr;
    });
  };
  draw();
  return { cv, draw };
}

export function createCake3D(canvas, opts = {}) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'low-power' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.setClearColor(0x000000, 0);
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  // Белый крем должен остаться белым, а не серым: света даём с запасом,
  // а пересветы мягко сжимает тональная кривая, не трогая оттенки
  renderer.toneMapping = THREE.NeutralToneMapping;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 60);
  const target = new THREE.Vector3(0, H * 0.4, 0);

  // Свет как на витринной съёмке: мягкий общий, основной слева сверху, слабый справа
  scene.add(new THREE.HemisphereLight(0xffffff, 0xe6dfd6, 1.6));
  const key = new THREE.DirectionalLight(0xfff7ef, 2.3);
  key.position.set(-3.2, 6, 4.2);
  key.castShadow = true;
  key.shadow.mapSize.set(1024, 1024);
  Object.assign(key.shadow.camera, { left: -2.6, right: 2.6, top: 2.6, bottom: -2.6, near: 1, far: 16 });
  key.shadow.radius = 5;
  key.shadow.bias = -0.0005;
  key.shadow.normalBias = 0.025;
  scene.add(key);
  const fill = new THREE.DirectionalLight(0xeef2ff, 0.45);
  fill.position.set(4, 2, 3);
  scene.add(fill);

  // Тень на «столе» — сам стол прозрачный, виден фон блока превью
  const ground = new THREE.Mesh(new THREE.PlaneGeometry(14, 14), new THREE.ShadowMaterial({ opacity: 0.15 }));
  ground.rotation.x = -Math.PI / 2;
  ground.position.y = -BOARD_H;
  ground.receiveShadow = true;
  scene.add(ground);

  // Всё, что крутится вместе с тортом
  const group = new THREE.Group();
  scene.add(group);

  const boardSide = new THREE.Mesh(
    new THREE.CylinderGeometry(BOARD_R, BOARD_R, BOARD_H, 128, 1, true),
    new THREE.MeshStandardMaterial({ color: 0xeeeae3, roughness: 0.7 }));
  boardSide.position.y = -BOARD_H / 2;
  boardSide.castShadow = true;
  group.add(boardSide);

  const board = boardCanvas(opts.boardText || '');
  const boardTex = new THREE.CanvasTexture(board.cv);
  boardTex.colorSpace = THREE.SRGBColorSpace;
  boardTex.anisotropy = renderer.capabilities.getMaxAnisotropy();
  const boardTop = new THREE.Mesh(new THREE.CircleGeometry(BOARD_R, 128),
    new THREE.MeshStandardMaterial({ map: boardTex, roughness: 0.7 }));
  boardTop.rotation.x = -Math.PI / 2;
  boardTop.receiveShadow = true;
  group.add(boardTop);
  if (document.fonts && opts.boardText) {
    document.fonts.load('italic 700 64px "Cormorant Garamond"').then(() => {
      board.draw();
      boardTex.needsUpdate = true;
      request();
    }).catch(() => {});
  }

  const sideMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.82, side: THREE.DoubleSide });
  const body = new THREE.Mesh(bodyGeometry(), sideMat);
  body.castShadow = true;
  body.receiveShadow = true;
  group.add(body);

  // Верх: текстура рисуется в конструкторе той же функцией, что и макет для WhatsApp.
  // CircleGeometry, повёрнутый плашмя: верх картинки уходит назад, низ — к зрителю,
  // ровно как в плоском превью.
  const topTex = new THREE.CanvasTexture(opts.topCanvas);
  topTex.colorSpace = THREE.SRGBColorSpace;
  topTex.anisotropy = renderer.capabilities.getMaxAnisotropy();
  const top = new THREE.Mesh(new THREE.CircleGeometry(R - BEVEL, 128),
    new THREE.MeshStandardMaterial({ map: topTex, roughness: 0.82 }));
  top.rotation.x = -Math.PI / 2;
  top.position.y = H;
  top.receiveShadow = true;
  group.add(top);

  // ---- Края ----
  const geos = {};
  const edgeMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.74, side: THREE.DoubleSide });
  let edgeMesh = null, edgeKind = 'none';

  function shellPlacement(cfg, ring, i) {
    const len = (2 * Math.PI * R / cfg.n) * cfg.overlap;
    const s = len / SHELL_LEN;
    const rho = ring === 'top' ? R - 0.55 * s : R + 0.45 * s;
    const y = ring === 'top' ? H - 0.012 : 0.002;
    // нижний ряд сдвинут на полшага — так ракушки ложатся «в ёлочку», как у кондитеров
    const th = (i + (ring === 'bottom' ? 0.5 : 0)) * 2 * Math.PI / cfg.n;
    return { th, rho, y, s };
  }

  function buildEdge(kind) {
    if (edgeMesh) {
      group.remove(edgeMesh);
      edgeMesh.dispose();
      edgeMesh = null;
    }
    edgeKind = kind;
    const cfg = EDGES[kind];
    if (!cfg) return;
    const shape = cfg.shape;
    if (!geos[shape]) geos[shape] = shape === 'ball' ? ballGeometry() : shellGeometry();
    const rings = [];
    if (cfg.top) rings.push('top');
    if (cfg.bottom) rings.push('bottom');
    const mesh = new THREE.InstancedMesh(geos[shape], edgeMat, cfg.n * rings.length);
    const m = new THREE.Matrix4(), q = new THREE.Quaternion(), sc = new THREE.Vector3();
    const pos = new THREE.Vector3(), up = new THREE.Vector3(0, 1, 0);
    const tan = new THREE.Vector3(), side = new THREE.Vector3();
    let k = 0;
    rings.forEach((ring) => {
      for (let i = 0; i < cfg.n; i++) {
        if (shape === 'ball') {
          const th = i * 2 * Math.PI / cfg.n;
          pos.set(cfg.rho * Math.cos(th), H - 0.02, cfg.rho * Math.sin(th));
          q.setFromAxisAngle(up, -th);   // хвостики смотрят одинаково относительно края
          sc.setScalar(cfg.size);
        } else {
          const pl = shellPlacement(cfg, ring, i);
          pos.set(pl.rho * Math.cos(pl.th), pl.y, pl.rho * Math.sin(pl.th));
          // ракушка лежит вдоль обода: длинная ось — по касательной
          tan.set(-Math.sin(pl.th), 0, Math.cos(pl.th));
          side.crossVectors(tan, up);
          m.makeBasis(tan, up, side);
          q.setFromRotationMatrix(m);
          sc.setScalar(pl.s);
        }
        m.compose(pos, q, sc);
        mesh.setMatrixAt(k++, m);
      }
    });
    mesh.instanceMatrix.needsUpdate = true;
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    group.add(mesh);
    edgeMesh = mesh;
  }

  // ---- Камера и кадр ----
  let elev = 0.62;    // наклон камеры, рад: сверху видно рисунок, сбоку — края
  const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function placeCamera() {
    const w = canvas.clientWidth || 1, h = canvas.clientHeight || 1;
    camera.aspect = w / h;
    const vfov = THREE.MathUtils.degToRad(camera.fov);
    const hfov = 2 * Math.atan(Math.tan(vfov / 2) * camera.aspect);
    const dist = FIT / Math.sin(Math.min(vfov, hfov) / 2);
    camera.position.set(0, target.y + Math.sin(elev) * dist, Math.cos(elev) * dist);
    camera.lookAt(target);
    camera.updateProjectionMatrix();
  }

  let raf = 0, anim = null, vel = 0, drag = null, lastX = 0, lastMoveT = 0, disposed = false, lost = false;
  function frame(now) {
    raf = 0;
    if (disposed || lost) return;
    let more = false;
    if (anim) {
      // метка кадра бывает чуть раньше момента нажатия — отрицательный ход не нужен
      const t = Math.max(0, Math.min(1, (now - anim.t0) / anim.dur));
      const e = 1 - Math.pow(1 - t, 3);
      group.rotation.y = anim.from + (anim.to - anim.from) * e;
      if (t < 1) more = true; else anim = null;
    }
    if (!drag && !anim && Math.abs(vel) > 0.0006) {   // докрутка после броска
      group.rotation.y += vel;
      vel *= 0.93;
      more = true;
    }
    placeCamera();
    renderer.render(scene, camera);
    if (more) request();
  }
  function request() {
    if (!raf && !disposed && !lost) raf = requestAnimationFrame(frame);
  }

  // ---- Вращение мышью и пальцем ----
  // По горизонтали крутим торт. По вертикали меняем наклон, но только мышью:
  // на телефоне вертикальный жест оставляем странице (touch-action: pan-y).
  // Ведёт только первый палец: второй (щипок) не должен дёргать торт.
  canvas.addEventListener('pointerdown', (e) => {
    if (e.button || drag) return;
    drag = { id: e.pointerId, x: e.clientX, y: e.clientY, rot: group.rotation.y, elev, touch: e.pointerType === 'touch' };
    lastX = e.clientX;
    lastMoveT = performance.now();
    vel = 0;
    anim = null;
    try { canvas.setPointerCapture(e.pointerId); } catch (err) { /* не критично */ }
    canvas.classList.add('drag');
  });
  canvas.addEventListener('pointermove', (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    group.rotation.y = drag.rot + (e.clientX - drag.x) * 0.012;
    vel = reduced ? 0 : Math.max(-0.25, Math.min(0.25, (e.clientX - lastX) * 0.012));
    lastX = e.clientX;
    lastMoveT = performance.now();
    if (!drag.touch) elev = Math.max(0.18, Math.min(1.25, drag.elev + (e.clientY - drag.y) * 0.006));
    request();
  });
  const end = (e) => {
    if (!drag || e.pointerId !== drag.id) return;
    drag = null;
    // палец остановили перед отпусканием или жест забрала прокрутка страницы
    if (e.type === 'pointercancel' || performance.now() - lastMoveT > 90) vel = 0;
    canvas.classList.remove('drag');
    request();
  };
  canvas.addEventListener('pointerup', end);
  canvas.addEventListener('pointercancel', end);
  canvas.addEventListener('lostpointercapture', end);

  // Телефоны отбирают WebGL у фоновой вкладки (переключились в WhatsApp и вернулись).
  // Это временно: three сам восстанавливает контекст, нам остаётся перерисовать кадр.
  canvas.addEventListener('webglcontextlost', (e) => {
    e.preventDefault();
    lost = true;
    if (raf) { cancelAnimationFrame(raf); raf = 0; }
  });
  canvas.addEventListener('webglcontextrestored', () => {
    lost = false;
    topTex.needsUpdate = true;
    boardTex.needsUpdate = true;
    request();
  });

  const ro = new ResizeObserver(() => {
    const w = canvas.clientWidth, h = canvas.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    request();
  });
  ro.observe(canvas);

  let cream = '';
  let ecolor = '';
  return {
    setCream(hex) {
      if (hex === cream) return;
      cream = hex;
      sideMat.color.set(hex);
      request();
    },
    setEdge(kind, hex) {
      if (hex !== ecolor) { ecolor = hex; edgeMat.color.set(hex); }
      if (kind !== edgeKind) buildEdge(kind);
      request();
    },
    topChanged() {
      topTex.needsUpdate = true;
      request();
    },
    resize() {
      const w = canvas.clientWidth, h = canvas.clientHeight;
      if (w && h) renderer.setSize(w, h, false);
      request();
    },
    rotateStep(dir) {
      vel = 0;
      if (reduced) {   // без анимации — сразу на новый угол
        anim = null;
        group.rotation.y += dir * Math.PI / 4;
      } else {
        const from = group.rotation.y;
        anim = { from, to: from + dir * Math.PI / 4, t0: performance.now(), dur: 480 };
      }
      request();
    },
    // Короткий доворот при первом показе — сразу видно, что торт объёмный и его можно крутить
    intro() {
      if (reduced) { request(); return; }
      group.rotation.y = -0.85;
      anim = { from: -0.85, to: 0, t0: performance.now(), dur: 1100 };
      request();
    },
    render: request,
    dispose() {
      disposed = true;
      ro.disconnect();
      renderer.dispose();
    },
  };
}
