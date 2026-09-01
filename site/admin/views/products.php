<?php
$editSlug = (string)($_GET['edit'] ?? '');
$edit = null;
foreach ($products as $p) if ($p['slug'] === $editSlug) { $edit = $p; break; }
$showForm = $edit || isset($_GET['add']);
$filter = (string)($_GET['type'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$seoOnly = isset($_GET['seo']);

// SEO товара: есть ли заголовок и описание и не слишком ли они длинные
function seo_state(array $p): array
{
    $t = trim($p['seo_title'] ?? '');
    $d = trim($p['seo_desc'] ?? '');
    if ($t === '' || $d === '') return ['no', 'не заполнено'];
    if (mb_strlen($t) > 60 || mb_strlen($d) > 160) return ['warn', 'длинновато'];
    return ['ok', mb_strlen($t) . ' / ' . mb_strlen($d)];
}

$noSeo = 0;
foreach ($products as $p) if (seo_state($p)[0] === 'no') $noSeo++;

$rows = array_filter($products, function ($p) use ($filter, $q, $seoOnly) {
    if ($filter && $p['type'] !== $filter) return false;
    if ($seoOnly && seo_state($p)[0] === 'ok') return false;
    if ($q !== '' && mb_stripos($p['title'], $q) === false && mb_stripos($p['slug'], $q) === false) return false;
    return true;
});
?>

<?php if ($showForm): ?>
<div class="card">
  <div class="card-hd"><h2><?= $edit ? 'Редактировать: ' . e($edit['title']) : 'Новый торт' ?></h2><a href="/admin/products">Отмена</a></div>
  <form method="post" enctype="multipart/form-data" class="pad">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="slug" value="<?= e($edit['slug'] ?? '') ?>">
    <div class="pair">
      <div>
        <label for="title">Название</label>
        <input type="text" name="title" id="title" value="<?= e($edit['title'] ?? '') ?>" required>

        <label for="type">Тип</label>
        <select name="type" id="type">
          <?php foreach (type_names() as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($edit['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>

        <label for="price">Цена</label>
        <input type="text" name="price" id="price" value="<?= e($edit['price'] ?? '') ?>" placeholder="25 – 60 ₼" required>

        <label for="photo">Фото <?= $edit ? '<span class="muted">(пусто — оставить прежнее)</span>' : '' ?></label>
        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" <?= $edit ? '' : 'required' ?>>
        <p class="hint">Кадрируется в квадрат 800×800. JPG, PNG или WebP до 10 МБ.</p>
      </div>
      <div>
        <?php if ($edit): ?>
        <label>Текущее фото</label>
        <img class="prev" src="<?= e($edit['img']) ?>" alt="">
        <label>Адрес страницы</label>
        <input type="text" value="/mehsul/<?= e($edit['slug']) ?>/" readonly>
        <p class="hint">Не меняется — так сохраняются позиции в поиске.</p>
        <?php endif; ?>

        <label for="seo_title">SEO-заголовок <span class="muted">(пусто — автоматически)</span> <i class="cnt" id="cnt-t"></i></label>
        <input type="text" name="seo_title" id="seo_title" value="<?= e($edit['seo_title'] ?? '') ?>" maxlength="120">

        <label for="seo_desc">SEO-описание <i class="cnt" id="cnt-d"></i></label>
        <textarea name="seo_desc" id="seo_desc" rows="4" maxlength="320"><?= e($edit['seo_desc'] ?? '') ?></textarea>

        <div class="serp">
          <span class="serp-url"><?= e(rtrim(CANON_HOST, '/')) ?>/mehsul/<?= e($edit['slug'] ?? 'novyy-tort') ?>/</span>
          <b id="serp-t"></b>
          <span id="serp-d"></span>
        </div>
        <p class="hint">Так карточка выглядит в поиске. Хорошая длина: заголовок до 60 символов, описание 120–160.</p>
      </div>
    </div>
    <div class="form-foot">
      <button>Сохранить</button>
      <a class="btn ghost" href="/admin/products">Отмена</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <div class="filters">
      <a href="/admin/products" class="chip <?= $filter === '' ? 'on' : '' ?>">Все</a>
      <?php foreach (type_names() as $k => $v): ?>
      <a href="/admin/products?type=<?= $k ?>" class="chip <?= $filter === $k ? 'on' : '' ?>"><?= $v ?></a>
      <?php endforeach; ?>
      <a href="/admin/products?seo=1" class="chip <?= $seoOnly ? 'on' : '' ?>">Проверить SEO<?= $noSeo ? ' <i>' . $noSeo . '</i>' : '' ?></a>
    </div>
    <form class="search" method="get">
      <?php if ($filter): ?><input type="hidden" name="type" value="<?= e($filter) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Поиск по названию…">
    </form>
  </div>

  <table class="grid">
    <thead><tr><th class="thumb"></th><th>Название</th><th class="hide-s">Тип</th><th class="hide-s">Цена</th><th>SEO</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td class="thumb"><img loading="lazy" src="<?= e($p['img']) ?>" alt=""></td>
        <td>
          <a href="/admin/products?edit=<?= e($p['slug']) ?>"><b><?= e($p['title']) ?></b></a>
          <small>/mehsul/<?= e($p['slug']) ?>/</small>
        </td>
        <td class="hide-s"><span class="pill <?= e($p['type']) ?>"><?= type_names()[$p["type"]] ?? e($p["type"]) ?></span></td>
        <td class="hide-s"><?= e($p['price']) ?></td>
        <?php [$st, $hint] = seo_state($p); ?>
        <td><span class="dot <?= $st === 'ok' ? 'ok' : ($st === 'warn' ? 'warn' : 'no') ?>" title="<?= e($hint) ?>"></span><small><?= e($hint) ?></small></td>
        <td class="right">
          <a class="btn ghost sm ico" href="/mehsul/<?= e($p['slug']) ?>/" target="_blank" rel="noopener" title="Открыть на сайте"><?= icon('open') ?><span>Открыть</span></a>
          <a class="btn ghost sm ico" href="/admin/products?edit=<?= e($p['slug']) ?>" title="Изменить"><?= icon('edit') ?><span>Изменить</span></a>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
            <button class="btn danger sm ico" data-confirm="Удалить «<?= e($p['title']) ?>» из каталога?" title="Удалить"><?= icon('trash') ?><span>Удалить</span></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="pad muted">Ничего не найдено.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($showForm): ?>
<script>
(function () {
  var t = document.getElementById('seo_title'), d = document.getElementById('seo_desc');
  var ct = document.getElementById('cnt-t'), cd = document.getElementById('cnt-d');
  var st = document.getElementById('serp-t'), sd = document.getElementById('serp-d');
  var title = document.getElementById('title');
  function upd() {
    var tv = t.value.trim() || ((title.value.trim() || 'Название') + ' - Vanilla.az');
    var dv = d.value.trim();
    ct.textContent = t.value.length + ' / 60';
    cd.textContent = dv.length + ' / 160';
    ct.className = 'cnt' + (t.value.length > 60 ? ' over' : '');
    cd.className = 'cnt' + (dv.length > 160 ? ' over' : (dv.length && dv.length < 70 ? ' low' : ''));
    st.textContent = tv.length > 60 ? tv.slice(0, 60) + '…' : tv;
    sd.textContent = dv ? (dv.length > 160 ? dv.slice(0, 160) + '…' : dv) : 'Описание пустое — Google подставит текст со страницы.';
  }
  [t, d, title].forEach(function (el) { if (el) el.addEventListener('input', upd); });
  upd();
})();
</script>
<?php endif; ?>
