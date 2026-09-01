<?php
$byType = ['bento' => 0, 'bantik' => 0, 'set' => 0, 'ctg' => 0];
foreach ($products as $p) { if (isset($byType[$p['type']])) $byType[$p['type']]++; }
$lastDesigns = array_slice($designs, 0, 6);
$updated = $catalog['updated'] ?? '';
?>
<div class="stats">
  <a class="stat hot" href="/admin/products"><span><?= count($products) ?></span>тортов в каталоге</a>
  <a class="stat" href="/admin/products?type=bento"><span><?= $byType['bento'] ?></span>бенто</a>
  <a class="stat" href="/admin/products?type=bantik"><span><?= $byType['bantik'] ?></span>бантиков</a>
  <a class="stat" href="/admin/products?type=set"><span><?= $byType['set'] ?></span>сетов</a>
  <a class="stat" href="/admin/products?type=ctg"><span><?= $byType['ctg'] ?></span>cake to go</a>
  <a class="stat" href="/admin/designs"><span><?= count($designs) ?></span>дизайнов клиентов</a>
</div>

<div class="cols">
  <div class="card">
    <div class="card-hd">
      <h2>Последние дизайны клиентов</h2>
      <a href="/admin/designs">Все →</a>
    </div>
    <?php if ($lastDesigns): ?>
    <div class="gal pad">
      <?php foreach ($lastDesigns as $d): ?>
      <a class="gal-item" href="/uploads/designs/<?= e($d['name']) ?>" target="_blank" rel="noopener">
        <img loading="lazy" src="/uploads/designs/<?= e($d['name']) ?>" alt="">
        <small><?= date('d.m.Y H:i', $d['time']) ?></small>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="pad muted">Пока никто не присылал свой дизайн. Здесь появятся макеты из конструктора и фото, которые загружают клиенты.</div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-hd"><h2>Быстрые действия</h2></div>
    <div class="pad quick">
      <a class="qbtn" href="/admin/products?add=1">
        <b>+ Добавить торт</b>
        <small>Фото, цена, тип и SEO</small>
      </a>
      <a class="qbtn" href="/admin/settings">
        <b>Контакты и карта</b>
        <small>Телефон, WhatsApp, адрес точки</small>
      </a>
      <a class="qbtn" href="/konstruktor/" target="_blank" rel="noopener">
        <b>Конструктор торта ↗</b>
        <small>Проверить, как видят клиенты</small>
      </a>
      <a class="qbtn" href="/bolme/bento-tort/" target="_blank" rel="noopener">
        <b>Каталог на сайте ↗</b>
        <small>Все торты глазами покупателя</small>
      </a>
    </div>
    <?php if ($updated): ?>
    <div class="pad hintline">Каталог обновлялся: <?= e($updated) ?></div>
    <?php endif; ?>
  </div>
</div>
