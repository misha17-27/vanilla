<?php
// Отзывы: скриншот + расшифровка. Текст нужен потому, что картинку поиск не читает,
// а слова клиентов — самое убедительное, что есть на сайте.
$rev   = json_decode((string)@file_get_contents(REVIEWS_FILE_ADM), true) ?: ['items' => []];
$items = $rev['items'] ?? [];
$withText = count(array_filter($items, fn($x) => trim((string)($x['text'] ?? '')) !== ''));
?>
<div class="head">
  <div>
    <h1>Отзывы</h1>
    <p class="muted">Скриншотов <?= count($items) ?>, с расшифровкой <?= $withText ?>. Текст показывается на странице отзывов и попадает в поиск — картинку Google не читает.</p>
  </div>
</div>

<form method="post" class="card pad">
  <input type="hidden" name="action" value="reviews_save">
  <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

  <div class="rev-adm">
    <?php foreach ($items as $i => $it): ?>
    <div class="rev-adm-row">
      <img src="<?= e($it['file']) ?>" alt="" loading="lazy">
      <div class="rev-adm-fields">
        <label for="ra-<?= $i ?>">Текст отзыва <span class="muted">#<?= $i + 1 ?></span></label>
        <textarea name="text[<?= $i ?>]" id="ra-<?= $i ?>" rows="4" maxlength="800"><?= e($it['text'] ?? '') ?></textarea>
        <label for="raa-<?= $i ?>">Автор <span class="muted">(ник в Instagram, без @)</span></label>
        <input type="text" name="author[<?= $i ?>]" id="raa-<?= $i ?>" value="<?= e($it['author'] ?? '') ?>" maxlength="80">
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="form-foot">
    <button class="btn">Сохранить</button>
    <span class="muted">Пустой текст — отзыв останется только картинкой.</span>
  </div>
</form>

<style>
.rev-adm{display:grid;gap:22px}
.rev-adm-row{display:grid;grid-template-columns:120px 1fr;gap:18px;align-items:start;padding-bottom:22px;border-bottom:1px solid var(--line)}
.rev-adm-row:last-child{border-bottom:0}
.rev-adm-row img{width:120px;border-radius:12px;display:block}
.rev-adm-fields label{margin-top:10px}
.rev-adm-fields label:first-child{margin-top:0}
@media (max-width:700px){.rev-adm-row{grid-template-columns:76px 1fr;gap:12px}.rev-adm-row img{width:76px}}
</style>
