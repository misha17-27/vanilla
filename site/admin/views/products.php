<?php
$editSlug = (string)($_GET['edit'] ?? '');
$edit = null;
foreach ($products as $p) if ($p['slug'] === $editSlug) { $edit = $p; break; }
$showForm = $edit || isset($_GET['add']);
$filter = (string)($_GET['type'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$rows = array_filter($products, function ($p) use ($filter, $q) {
    if ($filter && $p['type'] !== $filter) return false;
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

        <label for="seo_title">SEO-заголовок <span class="muted">(пусто — автоматически)</span></label>
        <input type="text" name="seo_title" id="seo_title" value="<?= e($edit['seo_title'] ?? '') ?>">

        <label for="seo_desc">SEO-описание</label>
        <textarea name="seo_desc" id="seo_desc" rows="4"><?= e($edit['seo_desc'] ?? '') ?></textarea>
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
    </div>
    <form class="search" method="get">
      <?php if ($filter): ?><input type="hidden" name="type" value="<?= e($filter) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Поиск по названию…">
    </form>
  </div>

  <table class="grid">
    <thead><tr><th class="thumb"></th><th>Название</th><th class="hide-s">Тип</th><th class="hide-s">Цена</th><th class="right"></th></tr></thead>
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
        <td class="right">
          <a class="btn ghost sm" href="/mehsul/<?= e($p['slug']) ?>/" target="_blank" rel="noopener">Открыть</a>
          <a class="btn ghost sm" href="/admin/products?edit=<?= e($p['slug']) ?>">Изменить</a>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
            <button class="btn danger sm" data-confirm="Удалить «<?= e($p['title']) ?>» из каталога?">Удалить</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="5" class="pad muted">Ничего не найдено.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
