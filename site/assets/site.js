// Mobile menu
var burger = document.getElementById('burger'), menu = document.getElementById('menu');
if (burger && menu) {
  function setMenu(open) {
    menu.classList.toggle('open', open);
    burger.classList.toggle('on', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.documentElement.classList.toggle('menu-open', open);
  }
  var menuClose = document.getElementById('menu-close');
  if (menuClose) menuClose.addEventListener('click', function () { setMenu(false); });
  burger.addEventListener('click', function () {
    setMenu(!menu.classList.contains('open'));
  });
  var narrow = window.matchMedia('(max-width:1060px)');
  menu.addEventListener('click', function (e) {
    var link = e.target.closest('a');
    if (!link) return;
    // на телефоне нажатие на раздел с подменю раскрывает список, а не уводит со страницы
    var parent = link.parentNode;
    if (narrow.matches && parent && parent.classList.contains('has-sub') && link.parentNode === parent) {
      e.preventDefault();
      parent.classList.toggle('open');
      return;
    }
    setMenu(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && menu.classList.contains('open')) setMenu(false);
  });
}
// В поля телефона пускаем только цифры и знаки номера — буквы отсекаем сразу
(function () {
  var phones = document.querySelectorAll('input[type="tel"]');
  if (!phones.length) return;
  function clean(v) { return v.replace(/[^\d+()\-\s]/g, ''); }
  Array.prototype.forEach.call(phones, function (inp) {
    inp.addEventListener('input', function () {
      var v = clean(inp.value);
      if (v === inp.value) return;
      var pos = inp.selectionStart - (inp.value.length - v.length);
      inp.value = v;
      try { inp.setSelectionRange(pos, pos); } catch (e) {}
    });
    inp.addEventListener('paste', function (e) {
      var txt = (e.clipboardData || window.clipboardData).getData('text');
      if (clean(txt) === txt) return;
      e.preventDefault();
      inp.setRangeText(clean(txt), inp.selectionStart, inp.selectionEnd, 'end');
      inp.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });
})();

// Кнопка «Категории» на телефоне: раскрывает список
(function () {
  var box = document.getElementById('cat-picker');
  var btn = document.getElementById('cat-picker-btn');
  if (!box || !btn) return;
  function open(v) {
    box.classList.toggle('open', v);
    btn.setAttribute('aria-expanded', v ? 'true' : 'false');
  }
  btn.addEventListener('click', function (e) { e.stopPropagation(); open(!box.classList.contains('open')); });
  document.addEventListener('click', function (e) { if (!box.contains(e.target)) open(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') open(false); });
  // выбрали категорию — закрываем и показываем её на кнопке
  box.addEventListener('click', function (e) {
    var tile = e.target.closest('.cat-tile');
    if (!tile) return;
    var b = tile.querySelector('b'), i = tile.querySelector('i');
    if (b) btn.querySelector('b').textContent = b.textContent;
    if (i) btn.querySelector('i').textContent = i.textContent;
    open(false);
  });
})();

// Фильтр каталога по категориям: плитки прячут лишние карточки без перезагрузки
(function () {
  var nav = document.querySelector('.cat-nav.is-filter');
  var grid = document.getElementById('cat-grid');
  if (!nav || !grid) return;
  var tiles = [].slice.call(nav.querySelectorAll('.cat-tile'));
  var cards = [].slice.call(grid.children);
  var empty = document.getElementById('cat-empty');

  function apply(cat, scroll) {
    var shown = 0;
    cards.forEach(function (c) {
      var ok = !cat || c.dataset.cat === cat;
      c.hidden = !ok;
      if (ok) shown++;
    });
    tiles.forEach(function (tl) { tl.classList.toggle('on', (tl.dataset.cat || '') === cat); });
    if (empty) empty.hidden = shown > 0;
    if (scroll) {
      var top = nav.getBoundingClientRect().top + window.pageYOffset - 90;
      window.scrollTo({ top: top, behavior: 'smooth' });
    }
  }

  nav.addEventListener('click', function (e) {
    var tile = e.target.closest('.cat-tile');
    if (!tile || e.metaKey || e.ctrlKey || e.shiftKey || e.button) return;
    e.preventDefault();
    var cat = tile.dataset.cat || '';
    history.replaceState(null, '', cat ? '#' + cat : location.pathname);
    apply(cat, true);
  });

  // адрес с якорем открывает нужную категорию (в т.ч. старые #bantik и #sets)
  var hash = location.hash.replace('#', '');
  if (hash === 'sets') hash = 'set';
  if (hash && tiles.some(function (tl) { return tl.dataset.cat === hash; })) apply(hash, false);
})();

// Language dropdown: закрываем по клику мимо и по Esc
var langBoxes = [].slice.call(document.querySelectorAll('details.lang'));
if (langBoxes.length) {
  document.addEventListener('click', function (e) {
    langBoxes.forEach(function (b) { if (b.open && !b.contains(e.target)) b.open = false; });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') langBoxes.forEach(function (b) { b.open = false; });
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
  var modal = document.getElementById('order-modal');
  var modalSend = document.getElementById('modal-send');
  var modalSum = document.getElementById('modal-sum-line');
  if (!sizeSel || !spongeSel || !fillSel || !priceEl || !orderEl || !modal) return;

  function refillFillings() {
    var items = cfg.sponges[spongeSel.selectedIndex][1];
    fillSel.innerHTML = items.map(function (i) {
      return '<option>' + i.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</option>';
    }).join('');
  }
  var designUrl = '';
  var extraLines = [];
  var selDate = null;
  var pickedPoint = null; // выбранная на карте точка доставки
  window.__setDesign = function (u) { designUrl = u; update(); };
  window.__setExtras = function (arr) { extraLines = arr || []; update(); };
  var dlSel = document.getElementById('opt-dl');
  var timeSel = document.getElementById('opt-time');
  var rowAddress = document.getElementById('row-address');
  var fAddress = document.getElementById('f-address');
  var fName = document.getElementById('f-name');
  var fPhone = document.getElementById('f-phone');
  var fOther = document.getElementById('f-other');
  var rowRname = document.getElementById('row-rname');
  var rowRphone = document.getElementById('row-rphone');
  var fRname = document.getElementById('f-rname');
  var fRphone = document.getElementById('f-rphone');
  var fText = document.getElementById('f-text');
  function pad2(n) { return (n < 10 ? '0' : '') + n; }
  function fmtDate(d) { return pad2(d.getDate()) + '.' + pad2(d.getMonth() + 1) + '.' + d.getFullYear(); }
  // Слоты времени: будни/по умолчанию 11:00–20:00, суббота — только 11:00–14:00
  function refreshSlots() {
    var lastStart = (selDate && selDate.getDay() === 6) ? 13 : 19;   // суббота — до 14:00
    var cur = timeSel.value;
    var html = '<option value="">' + timeSel.options[0].textContent + '</option>';
    for (var h = 11; h <= lastStart; h++) {
      var v = h + ':00–' + (h + 1) + ':00';
      html += '<option' + (v === cur ? ' selected' : '') + '>' + v + '</option>';
    }
    timeSel.innerHTML = html;
  }
  var boltNote = document.getElementById('bolt-note');
  function toggleFields() {
    rowAddress.hidden = dlSel.value !== 'courier';
    if (boltNote) boltNote.hidden = dlSel.value !== 'bolt';
    var other = fOther.checked;
    rowRname.hidden = !other;
    rowRphone.hidden = !other;
  }
  function update() {
    var size = cfg.sizes[sizeSel.selectedIndex];
    priceEl.textContent = size[1] + ' ₼';
    var lines = [
      cfg.intro,
      cfg.labels.size + ': ' + size[0],
      cfg.labels.sponge + ': ' + cfg.sponges[spongeSel.selectedIndex][0],
      cfg.labels.fill + ': ' + fillSel.value
    ];
    var lettering = fText ? fText.value.replace(/[\u0000-\u001F\u007F]/g, ' ').trim() : '';
    if (lettering) lines.push(cfg.labels.text + ': ' + lettering);
    extraLines.forEach(function (l) { lines.push(l); });
    if (selDate) lines.push(cfg.labels.date + ': ' + fmtDate(selDate));
    if (timeSel.value) lines.push(cfg.labels.time + ': ' + timeSel.value);
    lines.push(cfg.labels.dl + ': ' + dlSel.options[dlSel.selectedIndex].textContent);
    if (dlSel.value === 'courier' && fAddress.value.trim()) lines.push(cfg.labels.address + ': ' + fAddress.value.trim());
    if (dlSel.value === 'courier' && pickedPoint) {
      lines.push(cfg.pointLbl + ': https://maps.google.com/?q=' + pickedPoint.lat + ',' + pickedPoint.lng);
    }
    if (fName.value.trim()) lines.push(cfg.labels.name + ': ' + fName.value.trim());
    if (fPhone.value.trim()) lines.push(cfg.labels.phone + ': ' + fPhone.value.trim());
    if (fOther.checked && (fRname.value.trim() || fRphone.value.trim())) {
      lines.push(cfg.labels.recipient + ': ' + [fRname.value.trim(), fRphone.value.trim()].filter(Boolean).join(', '));
    }
    lines.push(cfg.linkLbl + ': ' + cfg.purl);
    if (designUrl) lines.push(cfg.design + ': ' + designUrl);
    modalSend.href = cfg.wa + encodeURIComponent(lines.join('\n'));
    modalSum.textContent = size[0] + ' · ' + size[1] + ' ₼';
  }
  // Попап оформления заказа
  function openModal() {
    modal.hidden = false;
    document.documentElement.classList.add('no-scroll');
  }
  function closeModal() {
    modal.hidden = true;
    document.documentElement.classList.remove('no-scroll');
  }
  // Проверка обязательных полей: имя, телефон и адрес при доставке курьером
  var errBox = document.getElementById('modal-err');
  function digits(s) { return (s.match(/\d/g) || []).length; }
  function validate() {
    var bad = [];
    if (!fName.value.trim()) bad.push(fName);
    if (digits(fPhone.value) < 7) bad.push(fPhone);
    if (dlSel.value === 'courier' && !fAddress.value.trim()) bad.push(fAddress);
    [fName, fPhone, fAddress].forEach(function (inp) {
      inp.classList.toggle('err', bad.indexOf(inp) > -1);
    });
    if (!bad.length) {
      if (errBox) errBox.hidden = true;
      return true;
    }
    if (errBox) {
      var onlyPhone = bad.length === 1 && bad[0] === fPhone && fPhone.value.trim();
      errBox.textContent = onlyPhone ? cfg.valPhone : cfg.valFill;
      errBox.hidden = false;
    }
    bad[0].focus();
    return false;
  }
  // Сохраняем заказ у себя, потом уже открываем WhatsApp
  function saveOrder() {
    var size = cfg.sizes[sizeSel.selectedIndex];
    var payload = {
      csrf: cfg.csrf,
      source: cfg.source || 'product',
      product: cfg.orderName || '',
      url: cfg.purl || '',
      size: size ? size[0] : '',
      price: size ? String(size[1]) + ' AZN' : '',
      sponge: cfg.sponges[spongeSel.selectedIndex] ? cfg.sponges[spongeSel.selectedIndex][0] : '',
      filling: fillSel.value,
      lettering: fText ? fText.value.trim() : '',
      date: selDate ? fmtDate(selDate) : '',
      time: timeSel.value,
      delivery: dlSel.options[dlSel.selectedIndex].textContent,
      address: dlSel.value === 'courier' ? fAddress.value.trim() : '',
      point: (dlSel.value === 'courier' && pickedPoint) ? (pickedPoint.lat + ',' + pickedPoint.lng) : '',
      name: fName.value.trim(),
      phone: fPhone.value.trim(),
      recipient: fOther.checked ? [fRname.value.trim(), fRphone.value.trim()].filter(Boolean).join(', ') : '',
      extras: extraLines.slice(),
      photos: designUrl ? [designUrl] : []
    };
    if (window.__orderPhotos) payload.photos = payload.photos.concat(window.__orderPhotos());
    var cf = modal.querySelector('[name="cf-turnstile-response"]');
    if (cf) payload.captcha = cf.value;
    try {
      fetch('/order-save.php', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        keepalive: true
      });
    } catch (e) { /* заказ всё равно уходит в WhatsApp */ }
  }
  modalSend.addEventListener('click', function (e) {
    if (!validate()) { e.preventDefault(); return; }
    saveOrder();
  });
  [fName, fPhone, fAddress].forEach(function (inp) {
    inp.addEventListener('input', function () {
      if (inp.classList.contains('err')) inp.classList.remove('err');
      if (errBox && !document.querySelector('.modal .txt.err')) errBox.hidden = true;
    });
  });
  // ---- Выбор точки доставки на карте (Leaflet + OpenStreetMap) ----
  var mapModal = document.getElementById('map-modal');
  var mapBtn = document.getElementById('map-btn');
  var mapObj = null, mapMarker = null, mapPoint = null, geoTimer = null;
  function openMap() {
    if (!window.L) return;
    mapModal.hidden = false;
    var start = pickedPoint || { lat: 40.4093, lng: 49.8671 }; // Баку
    if (!mapObj) {
      mapObj = L.map('map-canvas', { zoomControl: true }).setView([start.lat, start.lng], pickedPoint ? 17 : 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(mapObj);
      mapMarker = L.marker([start.lat, start.lng], { draggable: true }).addTo(mapObj);
      mapMarker.on('dragend', function () { setPoint(mapMarker.getLatLng()); });
      mapObj.on('click', function (e) { mapMarker.setLatLng(e.latlng); setPoint(e.latlng); });
    } else {
      mapObj.setView([start.lat, start.lng], pickedPoint ? 17 : 12);
      mapMarker.setLatLng([start.lat, start.lng]);
    }
    setTimeout(function () { mapObj.invalidateSize(); }, 60);
  }
  function closeMap() { mapModal.hidden = true; }
  // координаты -> адрес (Nominatim), с задержкой чтобы не спамить
  function setPoint(latlng) {
    mapPoint = { lat: +latlng.lat.toFixed(6), lng: +latlng.lng.toFixed(6) };
    var box = document.getElementById('map-addr');
    box.textContent = cfg.mapSearching;
    if (geoTimer) clearTimeout(geoTimer);
    geoTimer = setTimeout(function () {
      fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&accept-language=' + cfg.locale +
            '&lat=' + mapPoint.lat + '&lon=' + mapPoint.lng)
        .then(function (r) { return r.json(); })
        .then(function (d) {
          var a = d && d.address ? d.address : null;
          var parts = a ? [a.road, a.house_number, a.suburb || a.neighbourhood, a.city || a.town || a.village].filter(Boolean) : [];
          mapPoint.text = parts.length ? parts.join(', ') : (d && d.display_name ? d.display_name : '');
          box.textContent = mapPoint.text || (mapPoint.lat + ', ' + mapPoint.lng);
        })
        .catch(function () { box.textContent = mapPoint.lat + ', ' + mapPoint.lng; });
    }, 400);
  }
  if (mapBtn) {
    mapBtn.addEventListener('click', openMap);
    document.getElementById('map-close').addEventListener('click', closeMap);
    mapModal.addEventListener('click', function (e) { if (e.target === mapModal) closeMap(); });
    document.getElementById('map-locate').addEventListener('click', function () {
      if (!navigator.geolocation || !mapObj) return;
      navigator.geolocation.getCurrentPosition(function (pos) {
        var ll = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        mapObj.setView([ll.lat, ll.lng], 17);
        mapMarker.setLatLng([ll.lat, ll.lng]);
        setPoint(ll);
      });
    });
    document.getElementById('map-apply').addEventListener('click', function () {
      if (mapPoint) {
        pickedPoint = mapPoint;
        if (mapPoint.text && !fAddress.value.trim()) fAddress.value = mapPoint.text;
        fAddress.classList.remove('err');
        update();
      }
      closeMap();
    });
  }
  window.__mapPoint = function () { return pickedPoint; };

  orderEl.addEventListener('click', function () { update(); openModal(); });
  document.getElementById('modal-close').addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
  sizeSel.addEventListener('change', update);
  spongeSel.addEventListener('change', function () { refillFillings(); update(); });
  fillSel.addEventListener('change', update);
  dlSel.addEventListener('change', function () { toggleFields(); update(); });
  timeSel.addEventListener('change', update);
  fOther.addEventListener('change', function () { toggleFields(); update(); });
  [fAddress, fName, fPhone, fRname, fRphone, fText].forEach(function (inp) {
    if (inp) inp.addEventListener('input', update);
  });
  refreshSlots();
  toggleFields();
  update();

  // Календарь желаемой даты. Выходные приходят из админки: постоянные дни недели
  // и разовые даты — их клиент выбрать не может.
  var offWeekly = cfg.weekly || [0];
  var offDates  = cfg.closed || [];
  function isClosed(dt) {
    if (offWeekly.indexOf(dt.getDay()) > -1) return true;
    var iso = dt.getFullYear() + '-' + pad2(dt.getMonth() + 1) + '-' + pad2(dt.getDate());
    return offDates.indexOf(iso) > -1;
  }

  var dateBtn = document.getElementById('opt-date');
  var dateVal = document.getElementById('date-val');
  var cal = document.getElementById('cal');
  var calTitle = document.getElementById('cal-title');
  var calGrid = document.getElementById('cal-grid');
  var calPrev = document.getElementById('cal-prev');
  var calNext = document.getElementById('cal-next');
  if (dateBtn && cal) {
    var today = new Date(); today.setHours(0, 0, 0, 0);
    // Минимум 1 полный день на подготовку: сегодня заказ — завтра нельзя, ближайшая дата — послезавтра
    var minD = new Date(today); minD.setDate(minD.getDate() + 2);
    var maxD = new Date(today); maxD.setDate(maxD.getDate() + 60);
    var view = new Date(minD.getFullYear(), minD.getMonth(), 1);
    var mFmt = new Intl.DateTimeFormat(cfg.locale, { month: 'long', year: 'numeric' });
    var wdFmt = new Intl.DateTimeFormat(cfg.locale, { weekday: 'short' });
    function mIdx(d) { return d.getFullYear() * 12 + d.getMonth(); }
    function renderCal() {
      calTitle.textContent = mFmt.format(view);
      var html = '';
      for (var w = 0; w < 7; w++) { // 2024-01-01 — понедельник
        html += '<span class="cal-wd' + (w === 6 ? ' sun' : '') + '">' + wdFmt.format(new Date(2024, 0, 1 + w)) + '</span>';
      }
      var startIdx = (new Date(view.getFullYear(), view.getMonth(), 1).getDay() + 6) % 7;
      for (var b = 0; b < startIdx; b++) html += '<span></span>';
      var dim = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
      for (var d = 1; d <= dim; d++) {
        var dt = new Date(view.getFullYear(), view.getMonth(), d);
        var dis = dt < minD || dt > maxD || isClosed(dt);
        var sel = selDate && dt.getTime() === selDate.getTime();
        html += '<button type="button" class="cal-day' + (dis ? ' dis' : '') + (sel ? ' sel' : '') + '" data-d="' + d + '"' + (dis ? ' disabled' : '') + '>' + d + '</button>';
      }
      calGrid.innerHTML = html;
      calPrev.disabled = mIdx(view) <= mIdx(minD);
      calNext.disabled = mIdx(view) >= mIdx(maxD);
    }
    calGrid.addEventListener('click', function (e) {
      var b = e.target.closest('.cal-day');
      if (!b || b.disabled) return;
      selDate = new Date(view.getFullYear(), view.getMonth(), +b.getAttribute('data-d'));
      dateVal.textContent = fmtDate(selDate);
      dateVal.classList.add('has');
      cal.hidden = true;
      refreshSlots();
      update();
    });
    calPrev.addEventListener('click', function () { view.setMonth(view.getMonth() - 1); renderCal(); });
    calNext.addEventListener('click', function () { view.setMonth(view.getMonth() + 1); renderCal(); });
    dateBtn.addEventListener('click', function () {
      cal.hidden = !cal.hidden;
      if (!cal.hidden) renderCal();
    });
    document.addEventListener('click', function (e) {
      if (!cal.hidden && !cal.contains(e.target) && !dateBtn.contains(e.target)) cal.hidden = true;
    });
  }

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
            designUrl = res.url.indexOf('http') === 0 ? res.url : location.origin + res.url;
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
// Hero slider (по слайду на категорию)
(function () {
  var track = document.getElementById('hero-track');
  if (!track) return;
  var slides = [].slice.call(track.children);
  var dots = [].slice.call(document.querySelectorAll('#hero-dots button'));
  var i = 0, timer = null;
  function go(n) {
    i = (n + slides.length) % slides.length;
    slides.forEach(function (s, k) { s.classList.toggle('on', k === i); });
    dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
  }
  function start() { stop(); timer = setInterval(function () { go(i + 1); }, 5500); }
  function stop() { if (timer) clearInterval(timer); timer = null; }
  dots.forEach(function (d, k) {
    d.addEventListener('click', function () { go(k); start(); });
  });
  var prev = document.getElementById('hero-prev');
  var next = document.getElementById('hero-next');
  if (prev) prev.addEventListener('click', function () { go(i - 1); start(); });
  if (next) next.addEventListener('click', function () { go(i + 1); start(); });
  var hero = document.querySelector('.hero');
  hero.addEventListener('mouseenter', stop);
  hero.addEventListener('mouseleave', start);

  // Листание пальцем: горизонтальный жест переключает слайд,
  // вертикальный отдаём странице, чтобы не мешать прокрутке.
  var x0 = 0, y0 = 0, dx = 0, dy = 0, swiping = false;
  hero.addEventListener('touchstart', function (e) {
    if (e.touches.length !== 1) return;
    x0 = e.touches[0].clientX; y0 = e.touches[0].clientY;
    dx = dy = 0; swiping = true;
    stop();
  }, { passive: true });
  hero.addEventListener('touchmove', function (e) {
    if (!swiping) return;
    dx = e.touches[0].clientX - x0;
    dy = e.touches[0].clientY - y0;
  }, { passive: true });
  hero.addEventListener('touchend', function () {
    if (!swiping) return;
    swiping = false;
    if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) go(dx < 0 ? i + 1 : i - 1);
    start();
  }, { passive: true });

  start();
})();
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
