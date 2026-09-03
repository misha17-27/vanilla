<?php
$editKey = (string)($_GET['edit'] ?? '');
$edit    = $cats[$editKey] ?? null;
$showForm = $edit || isset($_GET['add']);

$counts = [];
foreach ($cats as $k => $c) $counts[$k] = 0;
foreach ($products as $p) if (isset($counts[$p['type']])) $counts[$p['type']]++;

$pageUrl = ['bento' => '/bolme/bento-tort/', 'ctg' => '/bolme/cake-to-go/'];
$seoData = load_seo();

// Состояние SEO категории — как в списке товаров
function cat_seo_state(array $seo): array
{
    $t = trim($seo['title'] ?? '');
    $d = trim($seo['desc'] ?? '');
    if ($t === '' || $d === '') return ['no', 'не заполнено'];
    if (mb_strlen($t) > 60 || mb_strlen($d) > 160) return ['warn', 'длинновато'];
    return ['ok', mb_strlen($t) . ' / ' . mb_strlen($d)];
}
$noSeo = 0;
foreach ($cats as $k => $c) if (cat_seo_state($seoData[cat_seo_key($k, $c)] ?? [])[0] === 'no') $noSeo++;
$urlOf = fn(array $c) => ($c['page'] ?? '') === 'own' ? cat_url($c) : ($pageUrl[$c['page'] ?? 'bento'] ?? '/');
?>

<?php if ($showForm): ?>
<div class="card">
  <div class="card-hd">
    <h2><?= $edit ? 'Категория: ' . e($edit['name']) : 'Новая категория' ?></h2>
    <a href="/admin/categories">Отмена</a>
  </div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="cat_save">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="key" value="<?= e($edit['key'] ?? '') ?>">
    <div class="pair">
      <div>
        <label for="c-name">Название (RU)</label>
        <input type="text" name="name" id="c-name" value="<?= e($edit['name'] ?? '') ?>" required>

        <label for="c-az">Название (AZ)</label>
        <input type="text" name="name_az" id="c-az" value="<?= e($edit['name_az'] ?? '') ?>">

        <label for="c-en">Название (EN)</label>
        <input type="text" name="name_en" id="c-en" value="<?= e($edit['name_en'] ?? '') ?>">
      </div>
      <div>
        <label for="c-page">Показывать на странице</label>
        <select name="page" id="c-page" <?= !empty($edit['builtin']) ? 'disabled' : '' ?>>
          <?php foreach (CAT_PAGES as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($edit['page'] ?? 'bento') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($edit['builtin'])): ?>
        <p class="hint">Базовая категория — её блок на сайте закреплён, чтобы не менялись адреса и позиции в поиске. Название можно править свободно.</p>
        <?php else: ?>
        <p class="hint">Отдельная страница — свой адрес, заголовок и SEO, пункт в меню сайта. Блоком — товары выйдут в конце выбранного раздела.</p>
        <?php if (($edit['page'] ?? '') === 'own'): ?>
        <label>Адрес страницы</label>
        <input type="text" value="<?= e(cat_url($edit)) ?>" readonly>
        <?php endif; ?>
        <?php endif; ?>

        <label for="c-desc">Описание под заголовком</label>
        <textarea name="desc" id="c-desc" rows="4"><?= e($edit['desc'] ?? '') ?></textarea>
        <p class="hint">Необязательно. Для базовых категорий текст берётся из переводов сайта.</p>
      </div>
    </div>

    <?php $curSeo = $edit ? ($seoData[cat_seo_key($editKey, $edit)] ?? []) : []; ?>
    <div class="seo-block">
      <div class="seo-head">
        <b>SEO страницы категории</b>
        <span class="muted">одно на все языки — как на vanilla.az</span>
      </div>
      <label for="c-seo-t">Заголовок (title) <i class="cnt" id="cnt-ct"></i></label>
      <input type="text" name="seo_title" id="c-seo-t" maxlength="120" value="<?= e($curSeo['title'] ?? '') ?>">
      <label for="c-seo-d">Описание (description) <i class="cnt" id="cnt-cd"></i></label>
      <textarea name="seo_desc" id="c-seo-d" rows="3" maxlength="320"><?= e($curSeo['desc'] ?? '') ?></textarea>
      <div class="serp">
        <span class="serp-url"><?= e(rtrim(CANON_HOST, '/')) ?><?= e($edit ? $urlOf($edit) : '/bolme/…/') ?></span>
        <b id="serp-ct"></b>
        <span id="serp-cd"></span>
      </div>
      <p class="hint">Хорошая длина: заголовок до 60 символов, описание 120–160. Пусто — подставим название категории.</p>
    </div>
    <div class="form-foot">
      <button>Сохранить</button>
      <a class="btn ghost" href="/admin/categories">Отмена</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <h2><?= count($cats) ?> категории каталога<?= $noSeo ? ' · без SEO: ' . $noSeo : '' ?></h2>
    <a href="/admin/categories?add=1">+ Добавить категорию</a>
  </div>
  <table class="grid">
    <thead><tr><th>Категория</th><th class="hide-s">Ключ</th><th>Товаров</th><th>SEO</th><th class="hide-s">Раздел на сайте</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($cats as $k => $c): ?>
      <tr>
        <td>
          <a href="/admin/categories?edit=<?= e($k) ?>"><b><?= e($c['name']) ?></b></a>
          <small><?= e(trim(($c['name_az'] ?? '') . ' · ' . ($c['name_en'] ?? ''), ' ·')) ?></small>
        </td>
        <td class="hide-s"><span class="pill <?= e($k) ?>"><?= e($k) ?></span></td>
        <td><a href="/admin/products?type=<?= e($k) ?>"><?= $counts[$k] ?? 0 ?> →</a></td>
        <?php [$st, $hint] = cat_seo_state($seoData[cat_seo_key($k, $c)] ?? []); ?>
        <td><span class="dot <?= $st === 'ok' ? 'ok' : ($st === 'warn' ? 'warn' : 'no') ?>" title="<?= e($hint) ?>"></span><small><?= e($hint) ?></small></td>
        <td class="hide-s"><?= e($urlOf($c)) ?></td>
        <td class="right">
          <a class="btn ghost sm ico" href="<?= e($urlOf($c)) ?>" target="_blank" rel="noopener" title="Открыть на сайте"><?= icon('open') ?><span>Открыть</span></a>
          <a class="btn ghost sm ico" href="/admin/categories?edit=<?= e($k) ?>" title="Изменить"><?= icon('edit') ?><span>Изменить</span></a>
          <?php if (empty($c['builtin'])): ?>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="cat_delete">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="key" value="<?= e($k) ?>">
            <button class="btn danger sm ico" data-confirm="Удалить категорию «<?= e($c['name']) ?>»?" title="Удалить"><?= icon('trash') ?><span>Удалить</span></button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pad hintline">Базовые категории (бенто, бантики, сеты, cake to go) повторяют структуру vanilla.az — их можно переименовать, но не удалить, чтобы адреса разделов и позиции в поиске не изменились.</div>
</div>

<?php if ($showForm): ?>
<script>
(function () {
  var t = document.getElementById('c-seo-t'), d = document.getElementById('c-seo-d');
  var name = document.getElementById('c-name');
  var ct = document.getElementById('cnt-ct'), cd = document.getElementById('cnt-cd');
  var st = document.getElementById('serp-ct'), sd = document.getElementById('serp-cd');
  if (!t || !d) return;
  function upd() {
    var tv = t.value.trim() || ((name.value.trim() || 'Категория') + ' - Vanilla.az');
    var dv = d.value.trim();
    ct.textContent = t.value.length + ' / 60';
    cd.textContent = dv.length + ' / 160';
    ct.className = 'cnt' + (t.value.length > 60 ? ' over' : '');
    cd.className = 'cnt' + (dv.length > 160 ? ' over' : (dv.length && dv.length < 70 ? ' low' : ''));
    st.textContent = tv.length > 60 ? tv.slice(0, 60) + '…' : tv;
    sd.textContent = dv || 'Описание пустое — Google подставит текст со страницы.';
  }
  [t, d, name].forEach(function (el) { if (el) el.addEventListener('input', upd); });
  upd();
})();
</script>
<?php endif; ?>
