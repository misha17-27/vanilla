<?php
// Навигация по категориям бенто-тортов — карточки с фото.
// $catNavActive — ключ текущего раздела: bento | bantik | sets | cat-{key}
$catNavImg = function (string $type): string {
    $items = products_of($type);
    return $items ? asset($items[0]['img']) : '';
};
$catNav = ['bento' => ['/bolme/bento-tort/', $t['nav_bento'], $catNavImg('bento'), count(products_of('bento'))]];
foreach (own_categories() as $c) {
    $catNav['cat-' . $c['key']] = [cat_url($c), cat_name($c['key']), $catNavImg($c['key']), count(products_of($c['key']))];
}
$catNav['bantik'] = ['/bolme/bento-tort/#bantik', $t['bantik_h'], $catNavImg('bantik'), count(products_of('bantik'))];
$catNav['sets']   = ['/bolme/bento-tort/#sets',   $t['sets_h'],   $catNavImg('set'),    count(products_of('set'))];
?>
<nav class="cat-nav" aria-label="<?= e($t['nav_bento']) ?>">
  <?php foreach ($catNav as $key => [$href, $label, $img, $count]): if (!$count) continue; ?>
  <a class="cat-tile <?= $key === ($catNavActive ?? '') ? 'on' : '' ?>" href="<?= e($href) ?>">
    <?php if ($img): ?><img loading="lazy" src="<?= e($img) ?>" alt="<?= e($label) ?>" width="72" height="72"><?php endif; ?>
    <span>
      <b><?= e($label) ?></b>
      <i><?= $count ?> <?= e($t['pcs']) ?></i>
    </span>
  </a>
  <?php endforeach; ?>
</nav>
