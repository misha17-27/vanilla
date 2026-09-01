<?php
$pages = [
    'home'    => ['Главная', '/'],
    'bento'   => ['Бенто-торты', '/bolme/bento-tort/'],
    'ctg'     => ['Cake to go', '/bolme/cake-to-go/'],
    'about'   => ['О нас', '/haqqimizda/'],
    'faq'     => ['FAQ', '/faq/'],
    'contact' => ['Контакты', '/elaqe/'],
];
$seoData = json_decode((string)@file_get_contents(__DIR__ . '/../../data/seo.json'), true) ?: [];
$noDesc = 0;
foreach ($products as $p) if (trim($p['seo_desc'] ?? '') === '') $noDesc++;
?>
<div class="card">
  <div class="card-hd">
    <h2>Заголовки и описания страниц</h2>
    <span class="muted">то, что видно в Google</span>
  </div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="seo">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <?php foreach ($pages as $key => [$label, $url]): ?>
    <div class="seo-block">
      <div class="seo-head">
        <b><?= e($label) ?></b>
        <a href="<?= e($url) ?>" target="_blank" rel="noopener"><?= e($url) ?> ↗</a>
      </div>
      <label for="t_<?= $key ?>">Заголовок (title)</label>
      <input type="text" id="t_<?= $key ?>" name="seo[<?= $key ?>][title]" value="<?= e($seoData[$key]['title'] ?? '') ?>" maxlength="120">
      <label for="d_<?= $key ?>">Описание (description)</label>
      <textarea id="d_<?= $key ?>" name="seo[<?= $key ?>][desc]" rows="2" maxlength="320"><?= e($seoData[$key]['desc'] ?? '') ?></textarea>
    </div>
    <?php endforeach; ?>
    <div class="form-foot"><button>Сохранить</button></div>
  </form>
</div>

<div class="card">
  <div class="card-hd"><h2>Проверка</h2></div>
  <div class="pad seo-check">
    <div class="chk ok">
      <b>Адреса страниц сохранены</b>
      <span>Товары открываются по тем же адресам, что и на старом сайте: <code>/mehsul/название/</code>. Разделы — <code>/bolme/bento-tort/</code>, <code>/bolme/cake-to-go/</code>, <code>/haqqimizda/</code>, <code>/faq/</code>, <code>/elaqe/</code>.</span>
    </div>
    <div class="chk ok">
      <b>Старые адреса перенаправляются</b>
      <span>Ссылки вида <code>/tortlar/</code>, <code>/biskvit/…</code>, <code>/reyler/</code> отправляют посетителя на нужную страницу (редирект 301) — ссылки из поиска не теряются.</span>
    </div>
    <div class="chk ok">
      <b>Карта сайта работает</b>
      <span><a href="/sitemap.xml" target="_blank" rel="noopener">/sitemap.xml</a> — <?= count($products) + 7 ?> адресов, обновляется автоматически при изменении каталога.</span>
    </div>
    <div class="chk <?= $noDesc ? 'warn' : 'ok' ?>">
      <b>Описания товаров</b>
      <span><?php if ($noDesc): ?>У <?= $noDesc ?> тортов пусто описание — Google подставит текст сам. Можно заполнить в карточке товара.<?php else: ?>Все товары с описаниями.<?php endif; ?></span>
    </div>
  </div>
</div>
