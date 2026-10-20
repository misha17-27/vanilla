// Визуальный редактор статьи блога (админка).
// Текст правится в contenteditable: абзацы, H2/H3, списки, жирный, курсив, ссылки,
// «совет» (blockquote). Особые блоки — торты, кнопка, таблица, фото, вопросы —
// плашки <div class="bw" data-block="{json}">, их правят в окне. В скрытое поле
// уходит HTML, на сервере blog_from_html() собирает из него блоки статьи.
(function () {
  var form = document.getElementById('b-form');
  if (!form) return;
  var data = JSON.parse(document.getElementById('bw-data').textContent);
  var PROD = data.products;   // slug -> [название, превью]
  var CATS = data.cats;       // [[ключ, название], …]
  var csrf = form.querySelector('[name=csrf]').value;
  var dlg = document.getElementById('bw-dlg');
  var dlgH = document.getElementById('bw-dlg-h');
  var dlgBody = document.getElementById('bw-dlg-body');
  try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }
  // разметка внутри полей блоков (**жирный**, [ссылка](/адрес/)) — для превью
  function inl(s) {
    return esc(s).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, '<u>$1</u>');
  }
  function targetName(t) {
    if (t === 'konstruktor') return 'конструктор';
    if (t === 'wa') return 'WhatsApp';
    if (t.indexOf('cat:') === 0) {
      for (var i = 0; i < CATS.length; i++) if ('cat:' + CATS[i][0] === t) return 'раздел «' + CATS[i][1] + '»';
    }
    return t;
  }
  var TITLES = { products: 'Карточки тортов', cta: 'Кнопка-приглашение', table: 'Таблица', img: 'Фото', faq: 'Вопросы и ответы' };

  function preview(b) {
    var h = '<div class="bw-h">' + TITLES[b.type] + '</div>';
    if (b.type === 'products') {
      h += '<div class="bw-prods">' + (b.slugs || []).map(function (s) {
        var p = PROD[s];
        return p ? '<figure><img src="' + esc(p[1]) + '" alt=""><figcaption>' + esc(p[0]) + '</figcaption></figure>'
                 : '<figure class="bad"><span>нет такого торта</span><figcaption>' + esc(s) + '</figcaption></figure>';
      }).join('') + '</div>';
    } else if (b.type === 'cta') {
      // надпись по умолчанию — как на сайте (blog_cta_target)
      var t = b.target || 'konstruktor';
      var def = t === 'konstruktor' ? 'Открыть конструктор' : t === 'wa' ? 'Заказать в WhatsApp' : t.indexOf('cat:') === 0 ? 'Смотреть торты' : 'Подробнее';
      h += '<div class="bw-cta"><div><b>' + inl(b.title || '') + '</b><span>' + inl(b.text || '') + '</span></div><i>'
         + esc(b.button || def) + ' → ' + esc(targetName(t)) + '</i></div>';
    } else if (b.type === 'table') {
      h += '<table><thead><tr>' + (b.head || []).map(function (c) { return '<th>' + inl(c) + '</th>'; }).join('') + '</tr></thead><tbody>'
         + (b.rows || []).map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + inl(c) + '</td>'; }).join('') + '</tr>'; }).join('')
         + '</tbody></table>';
    } else if (b.type === 'img') {
      h += '<div class="bw-img"><img src="' + esc(b.src) + '" alt=""><span>' + inl(b.caption || b.alt || '') + '</span></div>';
    } else if (b.type === 'faq') {
      h += '<dl class="bw-faq">' + (b.items || []).map(function (it) { return '<dt>' + esc(it.q) + '</dt><dd>' + inl(it.a) + '</dd>'; }).join('') + '</dl>';
    }
    return h;
  }
  function paint(w) {
    var b;
    try { b = JSON.parse(w.dataset.block); } catch (e) { w.remove(); return; }
    w.contentEditable = 'false';
    w.innerHTML = '<div class="bw-tools">'
      + '<button type="button" data-w="up" title="Выше">↑</button><button type="button" data-w="down" title="Ниже">↓</button>'
      + '<button type="button" data-w="edit">✎ Изменить</button><button type="button" data-w="del" title="Удалить">✕</button></div>'
      + preview(b);
  }
  function makeWidget(b) {
    var w = document.createElement('div');
    w.className = 'bw';
    w.dataset.block = JSON.stringify(b);
    paint(w);
    return w;
  }

  // ---------- вставка из Word / Google Docs: оставляем только нужное ----------
  var KEEP_BLOCK = { P: 1, H2: 1, H3: 1, UL: 1, OL: 1, LI: 1, BLOCKQUOTE: 1, TABLE: 1, THEAD: 1, TBODY: 1, TR: 1, TD: 1, TH: 1 };
  var MAP = { H1: 'H2', H4: 'H3', H5: 'H3', H6: 'H3', DIV: 'P' };
  var BLOCK_SEL = 'p,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,table,div';
  function cleanNode(n, out) {
    if (n.nodeType === 3) { out.appendChild(document.createTextNode(n.nodeValue)); return; }
    if (n.nodeType !== 1) return;
    // плашка из нашего же редактора (копирование статьи или вырезание куска)
    if (n.hasAttribute('data-block')) {
      try {
        var b = JSON.parse(n.getAttribute('data-block'));
        if (b && TITLES[b.type]) {
          var w = document.createElement('div');
          w.className = 'bw';
          w.setAttribute('contenteditable', 'false');
          w.setAttribute('data-block', JSON.stringify(b));
          out.appendChild(w);
        }
      } catch (e) {}
      return;
    }
    var tag = MAP[n.tagName] || n.tagName;
    if (/^(SCRIPT|STYLE|META|LINK|TITLE|IMG|SVG|IFRAME|OBJECT|BUTTON)$/.test(tag)) return;
    var st = (n.getAttribute('style') || '').toLowerCase();
    if (/mso-list\s*:\s*ignore/.test(st)) return;   // маркер «·» у пунктов списка из Word
    // пункт списка из Word — абзац с классом MsoListParagraph или стилем mso-list
    if (tag === 'P' && (/^MsoListParagraph/.test(n.className || '') || /mso-list\s*:/.test(st))) tag = 'LI';
    var notB = /font-weight\s*:\s*(normal|[1-5]00)\b/.test(st), notI = /font-style\s*:\s*normal/.test(st);
    // Google Docs заворачивает весь текст в <b style="font-weight:normal">: такая обёртка — не жирный
    var wrapsBlocks = (!KEEP_BLOCK[tag] || tag === 'P') && n.querySelector(BLOCK_SEL);
    var el = null;
    if (wrapsBlocks) el = null;
    else if (KEEP_BLOCK[tag]) el = document.createElement(tag);
    else if (!notB && (tag === 'B' || tag === 'STRONG' || /font-weight\s*:\s*(bold|[6-9]00)/.test(st))) el = document.createElement('strong');
    else if (!notI && (tag === 'I' || tag === 'EM' || /font-style\s*:\s*italic/.test(st))) el = document.createElement('em');
    else if (tag === 'A' && n.getAttribute('href')) { el = document.createElement('a'); el.setAttribute('href', n.getAttribute('href')); }
    else if (tag === 'BR') { out.appendChild(document.createElement('br')); return; }
    var target = el || out;
    for (var c = n.firstChild; c; c = c.nextSibling) cleanNode(c, target);
    if (el) out.appendChild(el);
  }
  function cleanHtml(html) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var box = document.createElement('div');
    for (var c = doc.body.firstChild; c; c = c.nextSibling) cleanNode(c, box);
    // пункты из Word идут подряд без <ul> — собираем их в список
    var list = null;
    [].slice.call(box.childNodes).forEach(function (n) {
      if (n.nodeType === 1 && n.tagName === 'LI') {
        if (!list) { list = document.createElement('ul'); box.insertBefore(list, n); }
        list.appendChild(n);
      } else if (!(n.nodeType === 3 && !n.nodeValue.trim())) {
        list = null;
      }
    });
    return box.innerHTML;
  }
  // простой текст: каждая строка — абзац (так пишут в WhatsApp, Telegram, Блокноте)
  function textToHtml(t) {
    return String(t).split(/\r?\n/).map(function (p) {
      p = p.trim();
      return p ? '<p>' + esc(p) + '</p>' : '';
    }).join('');
  }
  // выделение внутри поля, если в нём есть плашки: копируем их сами
  function widgetSelection(area) {
    var sel = window.getSelection();
    if (!sel.rangeCount) return null;
    var r = sel.getRangeAt(0);
    if (!area.contains(r.commonAncestorContainer) && r.commonAncestorContainer !== area) return null;
    var box = document.createElement('div');
    box.appendChild(r.cloneContents());
    if (!box.querySelector('.bw')) return null;
    box.querySelectorAll('.bw').forEach(function (w) { w.innerHTML = ''; });
    return { range: r, html: box.innerHTML, text: sel.toString() };
  }

  function inWidget(n) {
    var el = n && (n.nodeType === 1 ? n : n.parentNode);
    return el && el.closest ? el.closest('.bw') : null;
  }

  // ---------- один редактор на язык ----------
  var editors = [];
  function Editor(root) {
    var self = this;
    this.root = root;
    this.area = root.querySelector('.rt-area');
    this.html = root.querySelector('.rt-html');
    this.text = root.querySelector('.rt-text');
    this.mode = root.querySelector('.rt-mode');
    this.bar = root.querySelector('.rt-bar');
    this.toggleBtn = root.querySelector('.rt-toggle');
    this.helpHtml = root.parentNode.querySelector('.rt-help-html');
    this.helpText = root.parentNode.querySelector('.rt-help-text');
    this.range = null;
    this.hydrate();
    this.applyMode();

    this.area.addEventListener('input', function () { self.sync(); });
    this.area.addEventListener('paste', function (e) {
      var cd = e.clipboardData;
      if (!cd) return;
      e.preventDefault();
      var html = cd.getData('text/html');
      document.execCommand('insertHTML', false, html ? cleanHtml(html) : textToHtml(cd.getData('text/plain')));
      self.hydrate();
      self.sync();
    });
    // копирование и вырезание вместе с плашками: браузер их в буфер не кладёт
    ['copy', 'cut'].forEach(function (ev) {
      self.area.addEventListener(ev, function (e) {
        var c = widgetSelection(self.area);
        if (!c || !e.clipboardData) return;
        e.preventDefault();
        e.clipboardData.setData('text/html', c.html);
        e.clipboardData.setData('text/plain', c.text);
        if (ev === 'cut') { c.range.deleteContents(); self.hydrate(); self.changed(); }
      });
    });
    // Backspace и Delete рядом с плашкой её не удаляют — только кнопкой ✕
    this.area.addEventListener('keydown', function (e) {
      if (e.key !== 'Backspace' && e.key !== 'Delete') return;
      var sel = window.getSelection();
      if (!sel.rangeCount || !sel.isCollapsed) return;
      var r = sel.getRangeAt(0);
      if (r.startContainer === self.area) {
        var near = self.area.childNodes[e.key === 'Backspace' ? r.startOffset - 1 : r.startOffset];
        if (near && near.classList && near.classList.contains('bw')) e.preventDefault();
        return;
      }
      var blk = r.startContainer;
      while (blk && blk.parentNode !== self.area) blk = blk.parentNode;
      if (!blk) return;
      var probe = document.createRange();
      if (e.key === 'Backspace') { probe.setStart(blk, 0); probe.setEnd(r.startContainer, r.startOffset); }
      else { probe.setStart(r.startContainer, r.startOffset); probe.setEnd(blk, blk.nodeType === 3 ? blk.length : blk.childNodes.length); }
      if (probe.toString() !== '') return;   // курсор не у края абзаца
      var sib = e.key === 'Backspace' ? blk.previousElementSibling : blk.nextElementSibling;
      if (!sib || !sib.classList || !sib.classList.contains('bw')) return;
      e.preventDefault();
      // пустой абзац рядом с плашкой убираем, но место для текста до первой и после последней плашки оставляем
      var isSlot = blk === self.area.lastElementChild || blk === self.area.firstElementChild;
      if (blk.nodeType === 1 && blk.tagName === 'P' && !blk.textContent.trim() && !isSlot) { blk.remove(); self.changed(); }
    });
    // где стоит курсор — туда вставляем блоки
    document.addEventListener('selectionchange', function () {
      var s = window.getSelection();
      if (s.rangeCount && self.area.contains(s.getRangeAt(0).startContainer)) self.range = s.getRangeAt(0).cloneRange();
    });
    this.bar.addEventListener('mousedown', function (e) {
      var b = e.target.closest('button');
      if (!b) return;
      e.preventDefault();   // не теряем выделение в тексте
      if (b.dataset.cmd) self.command(b.dataset.cmd);
      if (b.dataset.add) openDialog(self, b.dataset.add, null);
    });
    this.area.addEventListener('click', function (e) {
      var t = e.target.closest('[data-w]');
      if (!t) return;
      var w = t.closest('.bw');
      var act = t.dataset.w;
      if (act === 'del') { if (confirm('Удалить этот блок из статьи?')) { w.remove(); self.changed(); } }
      else if (act === 'up' && w.previousElementSibling) { w.parentNode.insertBefore(w, w.previousElementSibling); self.changed(); }
      else if (act === 'down' && w.nextElementSibling) { w.parentNode.insertBefore(w.nextElementSibling, w); self.changed(); }
      else if (act === 'edit') openDialog(self, null, w);
    });
    this.toggleBtn.addEventListener('click', function () { self.toggle(); });
  }
  Editor.prototype.hydrate = function () {
    this.area.querySelectorAll('.bw').forEach(paint);
    if (!this.area.innerHTML.trim()) this.area.innerHTML = '<p><br></p>';
  };
  // в поле уходит HTML без превью плашек — сервер читает только data-block
  Editor.prototype.serialize = function () {
    var clone = this.area.cloneNode(true);
    clone.querySelectorAll('.bw').forEach(function (w) { w.innerHTML = ''; w.removeAttribute('contenteditable'); });
    return clone.innerHTML;
  };
  Editor.prototype.sync = function () {
    if (this.mode.value === 'html') this.html.value = this.serialize();
  };
  Editor.prototype.changed = function () {
    this.sync();
    form.dispatchEvent(new Event('input', { bubbles: true }));   // черновик и «есть несохранённые правки»
  };
  Editor.prototype.reload = function () {
    if (this.mode.value === 'html') { this.area.innerHTML = this.html.value; this.hydrate(); }
    this.applyMode();
  };
  Editor.prototype.applyMode = function () {
    var vis = this.mode.value !== 'text';
    this.area.hidden = !vis;
    this.bar.hidden = !vis;
    this.text.hidden = vis;
    if (this.helpHtml) this.helpHtml.hidden = !vis;
    if (this.helpText) this.helpText.hidden = vis;
    this.toggleBtn.textContent = vis ? 'Редактировать разметкой' : 'Вернуться к визуальному редактору';
  };
  Editor.prototype.command = function (cmd) {
    this.area.focus();
    if (this.range) { var s = window.getSelection(); s.removeAllRanges(); s.addRange(this.range); }
    // выделение, задевшее плашку краем (тройной щелчок), подрезаем до текста
    var sl = window.getSelection();
    if (sl.rangeCount) {
      var rr = sl.getRangeAt(0).cloneRange();
      var sw = inWidget(rr.startContainer), ew = inWidget(rr.endContainer);
      if (sw) rr.setStartAfter(sw);
      if (ew) rr.setEndBefore(ew);
      if ((sw || ew) && rr.collapsed) return;   // выделена только плашка
      sl.removeAllRanges(); sl.addRange(rr);
      // формат абзаца поверх плашки переставил бы текст и склеил абзацы
      if (/^(block:|insertUnorderedList|insertOrderedList|clear)/.test(cmd)
          && [].some.call(this.area.querySelectorAll('.bw'), function (w) { return rr.intersectsNode(w); })) {
        alert('Выделите текст без блоков (торты, кнопка, таблица…): оформление применяется к тексту между ними.');
        return;
      }
    }
    if (cmd.indexOf('block:') === 0) {
      var tag = cmd.slice(6);
      // повторное нажатие «совет» возвращает обычный абзац
      if (tag === 'BLOCKQUOTE' && this.currentBlock() && this.currentBlock().tagName === 'BLOCKQUOTE') tag = 'P';
      document.execCommand('formatBlock', false, tag);
    } else if (cmd === 'link') {
      var url = prompt('Адрес ссылки:\n• страница сайта — /bolme/bento-tort/ (без /az/ и /en/)\n• WhatsApp — wa\n• другой сайт — https://…', '/');
      if (url === null) return;
      url = url.trim();
      if (!/^(wa|\/(?!\/)[a-z0-9\/_-]*|https:\/\/\S+)$/i.test(url)) { alert('Такой адрес не подойдёт. Пример: /konstruktor/'); return; }
      if (window.getSelection().isCollapsed) { alert('Сначала выделите слова, которые станут ссылкой.'); return; }
      document.execCommand('createLink', false, url);
    } else if (cmd === 'clear') {
      document.execCommand('removeFormat', false, null);
      document.execCommand('unlink', false, null);
      document.execCommand('formatBlock', false, 'P');
    } else {
      document.execCommand(cmd, false, null);
    }
    this.changed();
  };
  // верхний блок (прямой ребёнок поля), в котором стоит курсор
  Editor.prototype.currentBlock = function () {
    if (!this.range) return null;
    var n = this.range.startContainer;
    // курсор между блоками: берём блок перед ним
    if (n === this.area) return this.area.childNodes[Math.max(0, this.range.startOffset - 1)] || null;
    while (n && n.parentNode !== this.area) n = n.parentNode;
    return n || null;
  };
  Editor.prototype.insertWidget = function (w) {
    var at = this.currentBlock();
    if (at && at.nextSibling) this.area.insertBefore(w, at.nextSibling);
    else if (at) this.area.appendChild(w);
    else this.area.appendChild(w);
    // после блока — пустой абзац, чтобы продолжить писать (пустые абзацы при сохранении отбрасываются)
    var nx = w.nextElementSibling;
    if (!nx || nx.tagName !== 'P' || nx.textContent.trim()) {
      nx = document.createElement('p');
      nx.innerHTML = '<br>';
      this.area.insertBefore(nx, w.nextSibling);
    }
    // пустой абзац, из которого вставляли, убираем
    if (at && at.nodeType === 1 && at.tagName === 'P' && !at.textContent.trim() && at !== w.nextElementSibling) at.remove();
    // курсор — в этот абзац: следующий блок встанет за новым, а не перед ним
    var after = document.createRange();
    after.setStart(nx, 0);
    after.collapse(true);
    this.range = after;
    this.area.focus({ preventScroll: true });
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(after.cloneRange());
    this.changed();
  };
  Editor.prototype.toggle = function () {
    var self = this;
    var toText = this.mode.value !== 'text';
    if (toText) this.sync();
    var fd = new FormData();
    fd.append('action', 'blog_convert');
    fd.append('csrf', csrf);
    fd.append('to', toText ? 'text' : 'html');
    fd.append('src', toText ? this.html.value : this.text.value);
    this.toggleBtn.disabled = true;
    fetch('/admin/', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.text(); })
      .then(function (t) {
        var res;
        try { res = JSON.parse(t); } catch (e) { res = null; }
        if (!res || !res.ok) throw new Error('bad');
        if (toText) { self.text.value = res.out; self.mode.value = 'text'; }
        else { self.html.value = res.out; self.mode.value = 'html'; self.area.innerHTML = res.out; self.hydrate(); }
        self.applyMode();
        self.changed();
      })
      .catch(function () { alert('Не получилось переключить редактор. Возможно, истекла сессия — текст в форме не пропал, сохраните статью.'); })
      .then(function () { self.toggleBtn.disabled = false; });
  };

  document.querySelectorAll('.rt').forEach(function (root) { editors.push(new Editor(root)); });
  window.BlogRT = {
    syncAll: function () { editors.forEach(function (e) { e.sync(); }); },
    reloadAll: function () { editors.forEach(function (e) { e.reload(); }); },
  };

  // ---------- окно редактирования блока ----------
  var dlgState = null;
  function field(label, html) { return '<label>' + label + '</label>' + html; }
  function input(id, val, attrs) { return '<input type="text" id="' + id + '" value="' + esc(val || '') + '" ' + (attrs || '') + '>'; }
  function area(id, val, rows) { return '<textarea id="' + id + '" rows="' + (rows || 3) + '">' + esc(val || '') + '</textarea>'; }
  function faqRow(q, a) {
    return '<div class="bw-qa"><input type="text" class="bw-q" placeholder="Вопрос" value="' + esc(q || '') + '">'
         + '<textarea class="bw-a" rows="2" placeholder="Ответ">' + esc(a || '') + '</textarea>'
         + '<button type="button" class="btn ghost sm" data-qa="del">✕</button></div>';
  }

  function openDialog(ed, type, w) {
    var b = w ? JSON.parse(w.dataset.block) : { type: type };
    type = b.type;
    dlgState = { ed: ed, w: w, type: type };
    dlgH.textContent = (w ? 'Изменить: ' : 'Добавить: ') + TITLES[type].toLowerCase();
    var h = '';
    if (type === 'products') {
      h = field('Торты — адреса через запятую', area('d-slugs', (b.slugs || []).join(', '), 3))
        + '<p class="hint">Найдите торт по названию и нажмите «Добавить» — или вставьте адреса из раздела «Товары». Лучше 3 или 6 тортов.</p>'
        + '<div class="bw-add"><input type="text" id="d-find" list="bw-prods" placeholder="Название торта…"><button type="button" class="btn ghost sm" id="d-find-add">Добавить</button></div>'
        + '<div class="bw-prods" id="d-prev"></div>';
    } else if (type === 'cta') {
      var t = b.target || 'konstruktor';
      var opts = [['konstruktor', 'Конструктор торта'], ['wa', 'WhatsApp']].concat(CATS.map(function (c) { return ['cat:' + c[0], 'Раздел: ' + c[1]]; }));
      var known = opts.some(function (o) { return o[0] === t; });
      h = field('Куда ведёт кнопка', '<select id="d-target">' + opts.map(function (o) { return '<option value="' + esc(o[0]) + '"' + (o[0] === t ? ' selected' : '') + '>' + esc(o[1]) + '</option>'; }).join('')
            + '<option value="custom"' + (known ? '' : ' selected') + '>Другая страница сайта…</option></select>')
        + '<div id="d-path-wrap"' + (known ? ' hidden' : '') + '>' + field('Адрес страницы', input('d-path', known ? '' : t, 'placeholder="/terkibler/"')) + '</div>'
        + field('Заголовок', input('d-title', b.title)) + field('Текст', area('d-text', b.text, 2))
        + field('Надпись на кнопке', input('d-button', b.button, 'placeholder="по умолчанию: «Открыть конструктор» / «Смотреть торты»"'));
    } else if (type === 'table') {
      var lines = [(b.head || ['', '']).join(' | ')].concat((b.rows || []).map(function (r) { return r.join(' | '); }));
      h = field('Строки таблицы', area('d-table', b.head ? lines.join('\n') : 'Размер | Вес | Цена\n1–2 персоны | 300–350 г | 25 ₼', 8))
        + '<p class="hint">Каждая строка — строка таблицы, ячейки разделяйте знаком «|». Первая строка — шапка.</p>';
    } else if (type === 'img') {
      h = field('Фото', input('d-src', b.src, 'list="b-covers" placeholder="/assets/img/products/…jpg"'))
        + field('Что на фото (alt)', input('d-alt', b.alt)) + field('Подпись под фото', input('d-caption', b.caption));
    } else if (type === 'faq') {
      h = '<div id="d-qa">' + ((b.items && b.items.length) ? b.items : [{ q: '', a: '' }]).map(function (it) { return faqRow(it.q, it.a); }).join('') + '</div>'
        + '<button type="button" class="btn ghost sm" id="d-qa-add">+ вопрос</button>';
    }
    dlgBody.innerHTML = h;
    wireDialog(type);
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
  }

  function wireDialog(type) {
    if (type === 'products') {
      var ta = document.getElementById('d-slugs'), pv = document.getElementById('d-prev');
      var upd = function () { pv.innerHTML = preview({ type: 'products', slugs: parseSlugs(ta.value) }).replace(/^<div class="bw-h">[^<]*<\/div>/, ''); };
      ta.addEventListener('input', upd);
      document.getElementById('d-find-add').addEventListener('click', function () {
        var f = document.getElementById('d-find'), v = f.value.trim();
        if (!PROD[v]) { for (var s in PROD) if (PROD[s][0].toLowerCase() === v.toLowerCase()) { v = s; break; } }
        if (!PROD[v]) { alert('Такого торта нет. Выберите из подсказок.'); return; }
        var list = parseSlugs(ta.value);
        if (list.indexOf(v) < 0) list.push(v);
        ta.value = list.join(', ');
        f.value = '';
        upd();
      });
      upd();
    } else if (type === 'cta') {
      var sel = document.getElementById('d-target');
      sel.addEventListener('change', function () { document.getElementById('d-path-wrap').hidden = sel.value !== 'custom'; });
    } else if (type === 'faq') {
      var box = document.getElementById('d-qa');
      document.getElementById('d-qa-add').addEventListener('click', function () { box.insertAdjacentHTML('beforeend', faqRow('', '')); });
      box.addEventListener('click', function (e) { if (e.target.dataset.qa === 'del') e.target.closest('.bw-qa').remove(); });
    }
  }
  function parseSlugs(v) {
    return v.split(/[\s,]+/).map(function (s) { return s.trim(); }).filter(function (s) { return /^[a-z0-9-]+$/.test(s); })
            .filter(function (s, i, a) { return a.indexOf(s) === i; });
  }
  function val(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; }

  function collect(type) {
    if (type === 'products') {
      var slugs = parseSlugs(val('d-slugs'));
      return slugs.length ? { type: 'products', slugs: slugs } : 'Добавьте хотя бы один торт.';
    }
    if (type === 'cta') {
      var t = val('d-target');
      if (t === 'custom') {
        t = val('d-path');
        if (!/^\/(?!\/)[a-z0-9\/_-]*$/i.test(t)) return 'Адрес страницы — например /terkibler/';
      }
      var o = { type: 'cta', target: t };
      ['title', 'text', 'button'].forEach(function (k) { var v = val('d-' + k); if (v) o[k] = v; });
      return o.title || o.text ? o : 'Напишите заголовок или текст кнопки-приглашения.';
    }
    if (type === 'table') {
      var rows = val('d-table').split(/\r?\n/).map(function (l) { return l.trim(); }).filter(Boolean)
        .map(function (l) { return l.replace(/^\||\|$/g, '').split('|').map(function (c) { return c.trim(); }); });
      if (!rows.length) return 'Таблица пустая.';
      return { type: 'table', head: rows[0], rows: rows.slice(1) };
    }
    if (type === 'img') {
      var src = val('d-src');
      if (!/^\/assets\/img\/[a-z0-9\/_.-]+\.(jpe?g|png|webp)$/i.test(src)) return 'Выберите фото из подсказок (адрес начинается с /assets/img/).';
      var im = { type: 'img', src: src, alt: val('d-alt') };
      if (val('d-caption')) im.caption = val('d-caption');
      return im;
    }
    if (type === 'faq') {
      var items = [];
      dlgBody.querySelectorAll('.bw-qa').forEach(function (r) {
        var q = r.querySelector('.bw-q').value.trim(), a = r.querySelector('.bw-a').value.trim();
        if (q && a) items.push({ q: q, a: a });
      });
      return items.length ? { type: 'faq', items: items } : 'Нужен хотя бы один вопрос с ответом.';
    }
    return 'Неизвестный блок';
  }

  document.getElementById('bw-ok').addEventListener('click', function (e) {
    e.preventDefault();
    if (!dlgState) return;
    var b = collect(dlgState.type);
    if (typeof b === 'string') { alert(b); return; }
    var st = dlgState;
    dlgState = null;
    dlg.close();   // закрытие возвращает в поле старый курсор — поэтому вставляем после него
    if (st.w) { st.w.dataset.block = JSON.stringify(b); paint(st.w); st.ed.changed(); }
    else st.ed.insertWidget(makeWidget(b));
  });
  document.getElementById('bw-cancel').addEventListener('click', function (e) { e.preventDefault(); dlg.close(); dlgState = null; });
  form.addEventListener('submit', function () { window.BlogRT.syncAll(); }, true);
})();
