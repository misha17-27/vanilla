<?php
$LANG_NAMES = ['ru' => 'RU', 'az' => 'AZ', 'en' => 'EN'];
$all      = blog_all();
$editSlug = (string)($_GET['edit'] ?? '');
$edit     = $all[$editSlug] ?? null;
$showForm = $edit || isset($_GET['add']);

$STATES = [
    'live'       => ['На сайте', 'ok'],
    'draft'      => ['Черновик', 'no'],
    'scheduled'  => ['Запланирована', 'warn'],
    'incomplete' => ['Не все языки', 'warn'],
];
$SOURCES = [
    'repo'     => 'исходная',
    'override' => 'изменена в админке',
    'admin'    => 'создана в админке',
];

// SEO-поля на одном языке: заполнены и в хорошей длине
function blog_seo_ok(array $p, string $l): string
{
    $t = mb_strlen(trim((string)($p[$l]['seo_title'] ?? '')));
    $d = mb_strlen(trim((string)($p[$l]['seo_desc'] ?? '')));
    if (!$t || !$d) return 'no';
    return ($t > 60 || $d < 120 || $d > 160) ? 'warn' : 'ok';
}

// Фото тортов для выбора обложки
$covers = [];
foreach ($products as $p) {
    if (!empty($p['img'])) $covers[asset($p['img'])] = $p['title'];
}
?>

<?php if ($showForm): ?>
<?php
$st   = $edit ? blog_state($edit) : 'draft';
$cur  = $edit ?: ['slug' => '', 'published' => blog_today(), 'image' => '', 'draft' => true];
$bodyText = [];
foreach ($LANG_NAMES as $l => $_) $bodyText[$l] = blog_to_text((array)($cur[$l]['body'] ?? []));
// Сохранение не прошло (нет обложки, адрес занят…) — показываем набранное, а не сохранённое
$refill = $_SESSION['blog_form'] ?? null;
unset($_SESSION['blog_form']);
if ($refill && (string)($refill['orig'] ?? '') !== (string)($edit['slug'] ?? '')) $refill = null;
$newSlug = '';
if ($refill) {
    if (($refill['published'] ?? '') !== '') $cur['published'] = $refill['published'];
    $cur['image'] = (string)($refill['image'] ?? '');
    $cur['draft'] = !empty($refill['draft']);
    $newSlug = (string)($refill['slug'] ?? '');
    foreach ($LANG_NAMES as $l => $_) {
        $rl = (array)($refill['l'][$l] ?? []);
        foreach (['title', 'seo_title', 'seo_desc', 'excerpt', 'image_alt'] as $k) $cur[$l][$k] = (string)($rl[$k] ?? '');
        $bodyText[$l] = (string)($rl['body'] ?? '');
    }
}
// только что сохранили — черновик из браузера больше не нужен
$justSaved = $edit && ($_SESSION['blog_saved'] ?? '') === $edit['slug'];
$justCreated = $justSaved && !empty($_SESSION['blog_created']);
unset($_SESSION['blog_saved'], $_SESSION['blog_created']);
?>
<div class="card">
  <div class="card-hd">
    <h2><?= $edit ? 'Статья: ' . e((string)($edit['ru']['title'] ?? $edit['slug'])) : 'Новая статья' ?></h2>
    <a href="/admin/blog">К списку</a>
  </div>
  <?php if ($edit): ?>
  <div class="pad b-status">
    <span class="dot <?= e($STATES[$st][1]) ?>"></span> <b><?= e($STATES[$st][0]) ?></b>
    <?php if ($st === 'incomplete'): ?> — не хватает: <?= e(strtoupper(implode(', ', blog_missing_langs($edit)))) ?><?php endif; ?>
    <?php if ($st === 'scheduled'): ?> — появится <?= e(date('d.m.Y', strtotime($edit['published']))) ?><?php endif; ?>
    <span class="muted">· <?= e($SOURCES[$edit['_src']] ?? '') ?></span>
    <?php if (!empty($edit['_stale'])): ?>
    · <b class="b-stale">исходную статью обновили в коде сайта уже после вашей правки — сверьте текст или сбросьте правки внизу страницы</b>
    <?php endif; ?>
    <?php if ($st === 'live'): ?>
    · <a href="<?= e('/blog/' . $edit['slug'] . '/') ?>" target="_blank" rel="noopener">открыть на сайте ↗</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="flash warn b-draft" id="b-draft" hidden>
    В этом браузере остался несохранённый текст статьи (<span></span>).
    <button type="button" class="btn sm" data-draft="restore">Вернуть его в форму</button>
    <button type="button" class="btn ghost sm" data-draft="drop">Удалить</button>
  </div>
  <form method="post" class="pad" enctype="multipart/form-data" id="b-form"
        data-key="<?= e($edit['slug'] ?? 'new') ?>" data-saved="<?= $justSaved ? '1' : '' ?>" data-created="<?= $justCreated ? '1' : '' ?>" data-new="<?= $edit ? '' : '1' ?>">
    <input type="hidden" name="action" value="blog_save">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="orig" value="<?= e($edit['slug'] ?? '') ?>">
    <input type="hidden" name="ver" value="<?= e($edit ? blog_version($edit['slug']) : '') ?>">

    <div class="pair">
      <div>
        <label for="b-slug">Адрес статьи</label>
        <?php if ($edit): ?>
        <input type="text" id="b-slug" value="/blog/<?= e($edit['slug']) ?>/" readonly>
        <p class="hint">Адрес не меняется: по нему статью уже знает поиск.</p>
        <?php else: ?>
        <input type="text" name="slug" id="b-slug" value="<?= e($newSlug) ?>" placeholder="например: tort-na-vypusknoy" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="80">
        <p class="hint">Латиница, цифры и дефис. Пусто — составим из заголовка на азербайджанском.</p>
        <?php endif; ?>

        <label for="b-pub">Дата публикации</label>
        <input type="date" name="published" id="b-pub" value="<?= e((string)($cur['published'] ?? blog_today())) ?>">
        <p class="hint">Дата в будущем — статья сама появится на сайте в этот день.</p>

        <label class="b-check"><input type="checkbox" name="draft" value="1" <?= !empty($cur['draft']) ? 'checked' : '' ?>> Черновик — не показывать на сайте</label>
      </div>
      <div>
        <label for="b-img">Обложка</label>
        <div class="b-cover-row">
          <img class="prev" id="b-img-prev" src="<?= e((string)($cur['image'] ?? '')) ?>" alt="" <?= empty($cur['image']) ? 'hidden' : '' ?>>
          <div>
            <input type="text" name="image" id="b-img" list="b-covers" value="<?= e((string)($cur['image'] ?? '')) ?>" placeholder="/assets/img/products/…jpg">
            <datalist id="b-covers">
              <?php foreach ($covers as $src => $name): ?><option value="<?= e($src) ?>"><?= e($name) ?></option><?php endforeach; ?>
            </datalist>
            <p class="hint">Начните вводить название торта или вставьте адрес фото. Или загрузите своё:</p>
            <input type="file" name="cover" id="b-file" accept="image/jpeg,image/png,image/webp">
          </div>
        </div>
      </div>
    </div>

    <div class="b-langs" role="tablist">
      <?php foreach ($LANG_NAMES as $l => $ln): $filled = $edit && !in_array($l, blog_missing_langs($edit), true); ?>
      <button type="button" class="chip <?= $l === 'ru' ? 'on' : '' ?>" data-tab="<?= $l ?>"><?= $ln ?> <i><?= $filled ? '✓' : '—' ?></i></button>
      <?php endforeach; ?>
      <span class="muted">все три языка нужны, чтобы статья вышла на сайт</span>
    </div>

    <?php foreach ($LANG_NAMES as $l => $ln): $lv = $cur[$l] ?? []; ?>
    <fieldset class="b-lang" data-lang="<?= $l ?>" <?= $l !== 'ru' ? 'hidden' : '' ?>>
      <div class="pair">
        <div>
          <label>Заголовок статьи (H1) · <?= $ln ?></label>
          <input type="text" name="l[<?= $l ?>][title]" maxlength="160" value="<?= e((string)($lv['title'] ?? '')) ?>">

          <label>SEO-заголовок (title) <i class="cnt" data-max="60"></i></label>
          <input type="text" name="l[<?= $l ?>][seo_title]" maxlength="120" value="<?= e((string)($lv['seo_title'] ?? '')) ?>" class="b-count">

          <label>SEO-описание (description) <i class="cnt" data-max="160" data-min="120"></i></label>
          <textarea name="l[<?= $l ?>][seo_desc]" rows="3" maxlength="320" class="b-count"><?= e((string)($lv['seo_desc'] ?? '')) ?></textarea>
        </div>
        <div>
          <label>Анонс для карточки в списке статей</label>
          <textarea name="l[<?= $l ?>][excerpt]" rows="3" maxlength="400"><?= e((string)($lv['excerpt'] ?? '')) ?></textarea>

          <label>Описание обложки (alt)</label>
          <input type="text" name="l[<?= $l ?>][image_alt]" maxlength="200" value="<?= e((string)($lv['image_alt'] ?? '')) ?>">
          <p class="hint">Что на фото: так его поймут поиск и люди с экранными чтецами.</p>
        </div>
      </div>

      <div class="b-editor">
        <div>
          <label>Текст статьи · <?= $ln ?></label>
          <textarea name="l[<?= $l ?>][body]" rows="30" class="b-code" spellcheck="true"><?= e($bodyText[$l]) ?></textarea>
        </div>
        <aside class="b-cheat">
          <b>Разметка</b>
          <code>## Подзаголовок</code>
          <code>### Подзаголовок поменьше</code>
          <span>Абзацы разделяйте пустой строкой.</span>
          <code>- пункт списка</code>
          <code>1. пункт по порядку</code>
          <code>&gt; совет в розовой рамке</code>
          <code>**жирный текст**</code>
          <code>[текст ссылки](/bolme/bento-tort/)</code>
          <span>Ссылки — без /az/ и /en/: язык добавится сам. <code>(wa)</code> — ссылка в WhatsApp.</span>
          <code>| Размер | Цена |<br>| 1–2 персоны | 25 ₼ |</code>
          <code>[товары: slug1, slug2, slug3]</code>
          <span>Карточки тортов: адреса из раздела «Товары».</span>
          <code>[кнопка: konstruktor | Заголовок | Текст | Надпись]</code>
          <span>Вместо konstruktor: <code>cat:usaq</code>, <code>wa</code> или адрес страницы.</span>
          <code>[фото: /assets/img/… | что на фото | подпись]</code>
          <code>? Вопрос<br>Ответ на него</code>
          <span>Вопросы подряд станут блоком «Частые вопросы».</span>
        </aside>
      </div>
    </fieldset>
    <?php endforeach; ?>

    <div class="form-foot">
      <button>Сохранить</button>
      <?php if ($edit): ?>
      <a class="btn ghost" href="/admin/blog?preview=<?= e($edit['slug']) ?>&amp;l=ru" target="_blank" rel="noopener" id="b-preview">Предпросмотр сохранённой версии ↗</a>
      <?php endif; ?>
      <a class="btn ghost" href="/admin/blog">Отмена</a>
    </div>
  </form>
  <?php if ($edit && in_array($edit['_src'], ['override', 'admin'], true)): ?>
  <form method="post" class="pad hintline">
    <input type="hidden" name="action" value="<?= $edit['_src'] === 'admin' ? 'blog_delete' : 'blog_reset' ?>">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="slug" value="<?= e($edit['slug']) ?>">
    <?php if ($edit['_src'] === 'admin'): ?>
    <button class="btn danger sm" data-confirm="Удалить статью «<?= e((string)($edit['ru']['title'] ?? $edit['slug'])) ?>»? Копия останется в резервных.">Удалить статью</button>
    <?php else: ?>
    <button class="btn ghost sm" data-confirm="Вернуть исходную версию статьи? Правки из админки пропадут (копия останется в резервных).">Сбросить правки — вернуть исходную версию</button>
    <span class="muted">Статья изменена в админке; исходная версия хранится в коде сайта.</span>
    <?php endif; ?>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <h2><?= count($all) ?> статей · на сайте: <?= count(array_filter($all, fn($p) => blog_state($p) === 'live')) ?></h2>
    <a href="/admin/blog?add=1">+ Новая статья</a>
  </div>
  <table class="grid">
    <thead><tr><th class="thumb"></th><th>Статья</th><th class="hide-s">Дата</th><th>Статус</th><th class="hide-s">SEO</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($all as $p): $st = blog_state($p); ?>
      <tr>
        <td class="thumb"><img loading="lazy" src="<?= e(thumb_url((string)($p['image'] ?? ''))) ?>" alt=""></td>
        <td>
          <a href="/admin/blog?edit=<?= e($p['slug']) ?>"><b><?= e((string)($p['ru']['title'] ?? $p['slug'])) ?></b></a>
          <small>/blog/<?= e($p['slug']) ?>/ · <?= e($SOURCES[$p['_src']] ?? '') ?><?= !empty($p['_stale']) ? ' · <b class="b-stale">исходная версия обновилась</b>' : '' ?></small>
        </td>
        <td class="hide-s"><?= e(date('d.m.Y', strtotime((string)($p['published'] ?? 'now')))) ?></td>
        <td class="b-stcell"><span class="dot <?= e($STATES[$st][1]) ?>"></span> <small><?= e($STATES[$st][0]) ?></small></td>
        <td class="hide-s b-seo">
          <?php foreach ($LANG_NAMES as $l => $ln): ?><span class="dot <?= blog_seo_ok($p, $l) ?>" title="<?= $ln ?>"></span><small><?= $ln ?></small><?php endforeach; ?>
        </td>
        <td class="right">
          <?php if ($st === 'live'): ?>
          <a class="btn ghost sm ico" href="/blog/<?= e($p['slug']) ?>/" target="_blank" rel="noopener" title="Открыть на сайте"><?= icon('open') ?><span>Открыть</span></a>
          <?php else: ?>
          <a class="btn ghost sm ico" href="/admin/blog?preview=<?= e($p['slug']) ?>&amp;l=ru" target="_blank" rel="noopener" title="Предпросмотр"><?= icon('eye') ?><span>Просмотр</span></a>
          <?php endif; ?>
          <a class="btn ghost sm ico" href="/admin/blog?edit=<?= e($p['slug']) ?>" title="Изменить"><?= icon('edit') ?><span>Изменить</span></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pad hintline">Статья выходит на сайт, когда заполнены все три языка, она не черновик и дата публикации наступила. Правки, сделанные здесь, сохраняются на сервере и не пропадают при обновлении сайта.</div>
</div>

<?php if ($showForm): ?>
<script>
(function () {
  var form = document.getElementById('b-form');
  if (!form) return;
  // вкладки языков
  var tabs = form.querySelectorAll('[data-tab]');
  var panes = form.querySelectorAll('[data-lang]');
  var prev = document.getElementById('b-preview');
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.toggle('on', x === t); });
      panes.forEach(function (p) { p.hidden = p.dataset.lang !== t.dataset.tab; });
      if (prev) prev.href = prev.href.replace(/l=\w+/, 'l=' + t.dataset.tab);
    });
  });
  // счётчики длины SEO-полей
  form.querySelectorAll('.b-count').forEach(function (inp) {
    var cnt = inp.previousElementSibling.querySelector('.cnt');
    function upd() {
      var n = inp.value.trim().length, max = +cnt.dataset.max, min = +(cnt.dataset.min || 0);
      cnt.textContent = n + ' / ' + max;
      cnt.className = 'cnt' + (n > max ? ' over' : (n && n < min ? ' low' : ''));
    }
    inp.addEventListener('input', upd);
    upd();
  });
  // превью обложки
  var img = document.getElementById('b-img'), pv = document.getElementById('b-img-prev');
  img.addEventListener('change', function () {
    if (/^\/assets\/img\//.test(img.value.trim())) { pv.src = img.value.trim(); pv.hidden = false; }
  });
  // не уйти со страницы с несохранёнными правками
  var dirty = false;
  form.addEventListener('input', function () { dirty = true; });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  // проверка до отправки: без обложки и с файлом больше 10 МБ сервер всё равно откажет
  var file = document.getElementById('b-file');
  form.addEventListener('submit', function (e) {
    var big = file.files[0] && file.files[0].size > 10 * 1024 * 1024;
    if (big || (form.dataset.new && !img.value.trim() && !file.files.length)) {
      e.preventDefault();
      alert(big ? 'Фото обложки больше 10 МБ — уменьшите его или выберите другое.' : 'Выберите обложку: фото торта из списка или загрузите своё.');
      return;
    }
    saveDraft();
    dirty = false;
  });

  // Черновик в браузере: если сессия истечёт или сохранение не пройдёт, текст не пропадёт.
  // Убираем его, только когда сервер подтвердил сохранение.
  var key = 'blog-draft:' + form.dataset.key;
  function fields() {
    var out = [];
    form.querySelectorAll('input[name], textarea[name]').forEach(function (el) {
      if (['csrf', 'action', 'ver', 'cover'].indexOf(el.name) > -1 || el.type === 'file') return;
      out.push([el.name, el.type === 'checkbox' ? (el.checked ? '1' : '') : el.value]);
    });
    return out;
  }
  function store(k, v) { try { if (v === null) localStorage.removeItem(k); else localStorage.setItem(k, v); } catch (err) {} }
  function load(k) { try { return localStorage.getItem(k); } catch (err) { return null; } }
  function saveDraft() { store(key, JSON.stringify({ t: Date.now(), f: fields() })); }
  if (form.dataset.saved) {
    store(key, null);
    if (form.dataset.created) store('blog-draft:new', null);
  } else {
    var raw = load(key), draft = null;
    try { draft = raw ? JSON.parse(raw) : null; } catch (err) { draft = null; }
    if (draft && JSON.stringify(draft.f) !== JSON.stringify(fields())) {
      var bar = document.getElementById('b-draft');
      bar.querySelector('span').textContent = new Date(draft.t).toLocaleString('ru-RU');
      bar.hidden = false;
      bar.addEventListener('click', function (e) {
        var act = e.target.dataset.draft;
        if (!act) return;
        if (act === 'restore') {
          draft.f.forEach(function (p) {
            var el = form.querySelector('[name="' + p[0].replace(/"/g, '') + '"]');
            if (!el) return;
            if (el.type === 'checkbox') el.checked = p[1] === '1'; else el.value = p[1];
            el.dispatchEvent(new Event('input', { bubbles: true }));
          });
          img.dispatchEvent(new Event('change'));
        } else {
          store(key, null);
        }
        bar.hidden = true;
      });
    }
  }
  var timer = 0;
  form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(saveDraft, 800); });

  // пока открыт редактор, сессия не должна истечь
  setInterval(function () { fetch('/admin/blog', { method: 'HEAD', credentials: 'same-origin' }).catch(function () {}); }, 4 * 60 * 1000);
})();
</script>
<?php endif; ?>
