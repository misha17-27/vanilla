// Чат-бот заказа: окно в углу сайта. Вопросы задаёт сервер (bot-web.php по сценарию
// data/bot-scenario.json) — окно только рисует сообщения и кнопки и отправляет ответы.
// Номер разговора хранится в localStorage: после перезагрузки страницы разговор продолжается.
(function () {
  var cfg = window.VCHAT;
  if (!cfg || !window.fetch) return;
  var T = cfg.t;
  var KEY = 'vchat_cid';

  var ICONS = {
    chat: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.6A8 8 0 1 1 21 12Z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01" stroke-width="2.6"/></svg>',
    person: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
    restart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>',
    send: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    photo: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="15" rx="3"/><circle cx="12" cy="12.5" r="3.5"/><path d="M8.5 5l1.5-2h4l1.5 2"/></svg>',
    link: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>',
    wa: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg>'
  };

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) { return '&#' + c.charCodeAt(0) + ';'; });
  }
  function store(v) {
    try { if (v === undefined) return localStorage.getItem(KEY); localStorage.setItem(KEY, v); } catch (e) { /* приватный режим */ }
    return null;
  }
  function newCid() {
    var a = new Uint8Array(16);
    (window.crypto || window.msCrypto).getRandomValues(a);
    return Array.prototype.map.call(a, function (b) { return (b < 16 ? '0' : '') + b.toString(16); }).join('');
  }
  var cid = store();
  if (!/^[0-9a-f]{32}$/.test(cid || '')) { cid = newCid(); store(cid); }

  // ===== Разметка =====
  var fab = document.createElement('button');
  fab.type = 'button';
  fab.className = 'vchat-fab';
  fab.setAttribute('aria-label', T.bot_btn);
  fab.innerHTML = ICONS.chat + '<span>' + esc(T.bot_btn) + '</span>';

  var box = document.createElement('div');
  box.className = 'vchat';
  box.hidden = true;
  box.setAttribute('role', 'dialog');
  box.setAttribute('aria-label', T.bot_title);
  box.innerHTML =
    '<div class="vchat-hd">' +
      '<span class="vchat-ava"><img src="/assets/favicon-192.png" alt="" width="32" height="32"></span>' +
      '<div class="vchat-who"><b>' + esc(T.bot_title) + '</b><small>' + esc(T.bot_sub) + '</small></div>' +
      '<button type="button" class="vchat-ib" data-act="human" title="' + esc(T.bot_human) + '" aria-label="' + esc(T.bot_human) + '">' + ICONS.person + '</button>' +
      '<button type="button" class="vchat-ib" data-act="restart" title="' + esc(T.bot_restart) + '" aria-label="' + esc(T.bot_restart) + '">' + ICONS.restart + '</button>' +
      '<button type="button" class="vchat-ib" data-act="close" title="' + esc(T.bot_close) + '" aria-label="' + esc(T.bot_close) + '">' + ICONS.close + '</button>' +
    '</div>' +
    '<div class="vchat-log" aria-live="polite"></div>' +
    '<form class="vchat-bar" novalidate>' +
      '<button type="button" class="vchat-att" hidden title="' + esc(T.bot_attach) + '" aria-label="' + esc(T.bot_attach) + '">' + ICONS.photo + '</button>' +
      '<input type="file" accept="image/jpeg,image/png,image/webp" hidden>' +
      '<input class="vchat-in" type="text" maxlength="400" autocomplete="off" placeholder="' + esc(T.bot_ph) + '" aria-label="' + esc(T.bot_ph) + '">' +
      '<button type="submit" class="vchat-send" aria-label="' + esc(T.bot_send) + '">' + ICONS.send + '</button>' +
    '</form>';
  document.body.appendChild(fab);
  document.body.appendChild(box);

  var log = box.querySelector('.vchat-log');
  var form = box.querySelector('.vchat-bar');
  var input = box.querySelector('.vchat-in');
  var attach = box.querySelector('.vchat-att');
  var file = box.querySelector('input[type=file]');
  var started = false;
  var busy = false;
  var captchaToken = '';
  var captchaBox = null;

  // ===== Связь с сервером =====
  function api(msg, done, retried) {
    fetch('/bot-web.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ csrf: cfg.csrf, cid: cid, lang: cfg.lang, msg: msg })
    }).then(function (r) { return r.json(); }).then(function (r) {
      // сессия истекла, пока окно было открыто: сервер выдал новый токен — повторяем
      if (!r.ok && r.error === 'csrf' && r.csrf && !retried) { cfg.csrf = r.csrf; api(msg, done, true); return; }
      done(r);
    }).catch(function () { done({ ok: false, error: 'net' }); });
  }

  function send(msg, echo) {
    if (busy) return;
    if (msg.type === 'option' && msg.id === 'confirm' && cfg.captcha) {
      if (!captchaToken) { note(T.bot_captcha); return; }
      msg.captcha = captchaToken;
    }
    if (echo) addUser(echo);
    freeze();
    busy = true;
    var dots = typing();
    api(msg, function (r) {
      busy = false;
      dots.remove();
      if (!r.ok) {
        if (r.error === 'captcha') { note(T.bot_captcha); resetCaptcha(); }
        else note(T.bot_err);
        unfreeze();
        return;
      }
      if (r.history) { log.innerHTML = ''; r.history.forEach(addMsg); } else { r.messages.forEach(addMsg); }
      unfreeze();
      var go = r.messages.filter(function (m) { return m.open; })[0];
      if (go) openNow(go.open);
    });
  }

  // Бот просит открыть ссылку сразу (WhatsApp после заказа). На телефоне — в этой же вкладке:
  // так откроется приложение. На компьютере — в новой; если браузер её не открыл — тоже здесь.
  function openNow(url) {
    var w = window.matchMedia('(pointer:coarse)').matches ? null : window.open(url, '_blank');
    if (w) w.opener = null;
    else location.href = url;
  }

  function start() {
    started = true;
    busy = true;
    var dots = typing();
    api({ type: 'start' }, function (r) {
      busy = false;
      dots.remove();
      if (!r.ok) { started = false; note(T.bot_err); return; }
      log.innerHTML = '';
      (r.history || []).forEach(addMsg);
      unfreeze();
    });
  }

  // ===== Сообщения =====
  function bubble(cls, html) {
    var d = document.createElement('div');
    d.className = 'vchat-msg ' + cls;
    d.innerHTML = html;
    log.appendChild(d);
    scroll();
    return d;
  }
  function addUser(m) {
    if (m.photo) bubble('user', '<img src="' + esc(m.photo) + '" alt="">');
    else if (m.text) bubble('user', esc(m.text));
  }
  function addMsg(m) {
    if (m.from === 'user') { addUser(m); return; }
    var b = bubble('bot', esc(m.text));
    if (m.step) { b.dataset.step = m.step; b.dataset.input = m.input || 'text'; b.dataset.ph = m.placeholder || ''; }
    if (!m.options || !m.options.length) return;
    var wrap = document.createElement('div');
    wrap.className = 'vchat-opts';
    // ссылка среди вариантов ответа (конструктор) выглядит как остальные варианты
    var solo = m.options.every(function (o) { return o.url; });
    m.options.forEach(function (o) {
      var btn;
      if (o.url) {
        btn = document.createElement('a');
        btn.href = o.url;
        var wa = /^https:\/\/wa\.me\//.test(o.url);
        btn.className = 'vchat-opt' + (solo || wa ? ' link' : '') + (wa ? ' wa' : '');
        btn.innerHTML = (wa ? ICONS.wa : '') + esc(o.label) + (wa ? '' : ICONS.link);
        if (btn.host !== location.host) { btn.target = '_blank'; btn.rel = 'noopener'; }
        else btn.addEventListener('click', function (e) {
          // страница сайта — в этой же вкладке, разговор там продолжится; если уже на ней — закрываем чат
          if (btn.pathname === location.pathname) { e.preventDefault(); setOpen(false); }
        });
      } else {
        btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'vchat-opt';
        btn.textContent = o.label;
        btn.addEventListener('click', function () {
          btn.classList.add('on');
          var msg = { type: 'option', id: o.id, label: o.label };
          if (m.step) msg.step = m.step;
          send(msg, { text: o.label });
        });
      }
      wrap.appendChild(btn);
    });
    log.appendChild(wrap);
    if (m.step === 'summary' && cfg.captcha) captcha(wrap);
    scroll();
  }
  function note(text) {
    bubble('note', esc(text));
  }
  function typing() {
    var d = document.createElement('div');
    d.className = 'vchat-typing';
    d.innerHTML = '<i></i><i></i><i></i>';
    log.appendChild(d);
    scroll();
    return d;
  }
  function scroll() {
    log.scrollTop = log.scrollHeight;
  }

  // Пока ждём ответ, кнопки не нажимаются. Потом живые — только у последнего вопроса:
  // на старые варианты выше в переписке нажимать уже поздно.
  function freeze() {
    [].forEach.call(log.querySelectorAll('button.vchat-opt'), function (b) { b.disabled = true; });
  }
  function unfreeze() {
    var groups = [].filter.call(log.querySelectorAll('.vchat-opts'), function (g) { return g.querySelector('button'); });
    var last = groups[groups.length - 1];
    groups.forEach(function (g) {
      g.classList.toggle('past', g !== last);
      [].forEach.call(g.querySelectorAll('button'), function (b) {
        b.disabled = g !== last;
        if (g === last) b.classList.remove('on');
      });
    });
    var asks = log.querySelectorAll('.vchat-msg.bot[data-step]');
    var q = asks[asks.length - 1];
    var mode = q ? q.dataset.input : 'text';
    attach.hidden = mode !== 'photo';
    input.type = mode === 'phone' ? 'tel' : 'text';
    input.inputMode = mode === 'phone' ? 'tel' : 'text';
    input.placeholder = (q && q.dataset.ph) || T.bot_ph;
  }

  // ===== Капча Cloudflare Turnstile — на шаге подтверждения, если включена в админке =====
  function captcha(wrap) {
    captchaToken = '';
    captchaBox = document.createElement('div');
    captchaBox.className = 'vchat-cap';
    wrap.parentNode.insertBefore(captchaBox, wrap);
    var target = captchaBox;
    withTurnstile(function () {
      window.turnstile.render(target, {
        sitekey: cfg.captcha,
        size: 'flexible',
        theme: 'light',
        callback: function (tok) { captchaToken = tok; },
        'expired-callback': function () { captchaToken = ''; }
      });
      scroll(); // капча выросла под сводкой — кнопки «Всё верно / Изменить» снова в кадре
    });
  }
  function resetCaptcha() {
    captchaToken = '';
    if (captchaBox && window.turnstile) window.turnstile.reset(captchaBox.firstChild || captchaBox);
  }
  function withTurnstile(fn) {
    if (window.turnstile) { fn(); return; }
    var s = document.createElement('script');
    s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    s.async = true;
    s.onload = fn;
    document.head.appendChild(s);
  }

  // ===== Фото: та же загрузка, что у «своего дизайна» на странице товара =====
  attach.addEventListener('click', function () { file.click(); });
  file.addEventListener('change', function () {
    var f = file.files[0];
    file.value = '';
    if (!f || busy) return;
    var fd = new FormData();
    fd.append('photo', f);
    fd.append('csrf', cfg.csrf);
    busy = true;
    var dots = typing();
    fetch('/upload-design.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        busy = false;
        dots.remove();
        if (!r.ok) { note(T.bot_upload_err); return; }
        send({ type: 'photo', url: r.url }, { photo: r.url });
      })
      .catch(function () { busy = false; dots.remove(); note(T.bot_upload_err); });
  });

  // ===== Управление окном =====
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = input.value.trim();
    if (!text || busy) return;
    input.value = '';
    send({ type: 'text', text: text }, { text: text });
  });
  box.querySelector('.vchat-hd').addEventListener('click', function (e) {
    var b = e.target.closest('[data-act]');
    if (!b) return;
    if (b.dataset.act === 'close') setOpen(false);
    if (b.dataset.act === 'human') send({ type: 'human', label: T.bot_human }, { text: T.bot_human });
    if (b.dataset.act === 'restart') { log.innerHTML = ''; send({ type: 'restart' }); }
  });
  function setOpen(open) {
    box.hidden = !open;
    document.documentElement.classList.toggle('vchat-open', open);
    if (!open) { fab.focus(); return; }
    if (!started) start();
    // на телефоне клавиатура сама не выскакивает — фокус только на компьютере
    if (window.matchMedia('(min-width:641px)').matches) input.focus();
  }
  fab.addEventListener('click', function () { setOpen(true); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !box.hidden) setOpen(false);
  });
  // vanilla.az/#chat — ссылка, которая сразу открывает чат (для Instagram, визиток, рекламы)
  if (location.hash === '#chat') setOpen(true);
})();
