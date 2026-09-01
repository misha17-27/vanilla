<?php
$editKey = (string)($_GET['edit'] ?? '');
$edit    = $cats[$editKey] ?? null;
$showForm = $edit || isset($_GET['add']);

$counts = [];
foreach ($cats as $k => $c) $counts[$k] = 0;
foreach ($products as $p) if (isset($counts[$p['type']])) $counts[$p['type']]++;

$pageUrl = ['bento' => '/bolme/bento-tort/', 'ctg' => '/bolme/cake-to-go/'];
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
        <p class="hint">Товары этой категории выйдут отдельным блоком в конце выбранного раздела.</p>
        <?php endif; ?>

        <label for="c-desc">Описание под заголовком</label>
        <textarea name="desc" id="c-desc" rows="4"><?= e($edit['desc'] ?? '') ?></textarea>
        <p class="hint">Необязательно. Для базовых категорий текст берётся из переводов сайта.</p>
      </div>
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
    <h2><?= count($cats) ?> категории каталога</h2>
    <a href="/admin/categories?add=1">+ Добавить категорию</a>
  </div>
  <table class="grid">
    <thead><tr><th>Категория</th><th class="hide-s">Ключ</th><th>Товаров</th><th class="hide-s">Раздел на сайте</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($cats as $k => $c): ?>
      <tr>
        <td>
          <a href="/admin/categories?edit=<?= e($k) ?>"><b><?= e($c['name']) ?></b></a>
          <small><?= e(trim(($c['name_az'] ?? '') . ' · ' . ($c['name_en'] ?? ''), ' ·')) ?></small>
        </td>
        <td class="hide-s"><span class="pill <?= e($k) ?>"><?= e($k) ?></span></td>
        <td><a href="/admin/products?type=<?= e($k) ?>"><?= $counts[$k] ?? 0 ?> →</a></td>
        <td class="hide-s"><?= e($pageUrl[$c['page'] ?? 'bento'] ?? '—') ?></td>
        <td class="right">
          <a class="btn ghost sm" href="<?= e($pageUrl[$c['page'] ?? 'bento'] ?? '/') ?>" target="_blank" rel="noopener">Открыть</a>
          <a class="btn ghost sm" href="/admin/categories?edit=<?= e($k) ?>">Изменить</a>
          <?php if (empty($c['builtin'])): ?>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="cat_delete">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="key" value="<?= e($k) ?>">
            <button class="btn danger sm" data-confirm="Удалить категорию «<?= e($c['name']) ?>»?">Удалить</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pad hintline">Базовые категории (бенто, бантики, сеты, cake to go) повторяют структуру vanilla.az — их можно переименовать, но не удалить, чтобы адреса разделов и позиции в поиске не изменились.</div>
</div>
