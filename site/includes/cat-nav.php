<?php
// Навигация по категориям бенто-тортов — карточки с фото.
// $catNavActive — ключ текущего раздела: bento | bantik | sets | cat-{key}
$catNavImg = function (string $type): string {
    $items = products_of($type);
    return $items ? asset($items[0]['img']) : '';
};
// $catNavFilter — на странице «Бенто-торты» плитки фильтруют общую сетку
$catNavFilter = $catNavFilter ?? false;
$catNav = [];
if ($catNavFilter) {
    $catNav['all'] = ['/bolme/bento-tort/', $t['cat_all'], '', 0, ''];
}
$catNav['bento'] = ['/bolme/bento-tort/', $t['nav_bento'], $catNavImg('bento'), count(products_of('bento')), 'bento'];
foreach (own_categories() as $c) {
    $catNav['cat-' . $c['key']] = [cat_url($c), cat_name($c['key']), $catNavImg($c['key']), count(products_of($c['key'])), $c['key']];
}
if ($catNavFilter) {
    $catNav['all'][3] = array_sum(array_map(fn($r) => $r[3], $catNav));
    $catNav['all'][2] = $catNav['bento'][2];
}
?>
<nav class="cat-nav<?= $catNavFilter ? ' is-filter' : '' ?>" aria-label="<?= e($t['nav_bento']) ?>">
  <?php foreach ($catNav as $key => [$href, $label, $img, $count, $catKey]): if (!$count) continue; ?>
  <a class="cat-tile <?= $key === ($catNavActive ?? '') ? 'on' : '' ?>" href="<?= e($href) ?>" data-cat="<?= e($catKey) ?>">
    <?php if ($img): ?><img loading="lazy" src="<?= e($img) ?>" alt="<?= e($label) ?>" width="72" height="72"><?php endif; ?>
    <span>
      <b><?= e($label) ?></b>
      <i><?= $count ?> <?= e($t['pcs']) ?></i>
    </span>
  </a>
  <?php endforeach; ?>
</nav>
