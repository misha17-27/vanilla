<?php
$byType = ['bento' => 0, 'bantik' => 0, 'set' => 0, 'ctg' => 0];
foreach ($products as $p) { if (isset($byType[$p['type']])) $byType[$p['type']]++; }
$lastDesigns = array_slice($designs, 0, 6);
$lastOrders  = array_slice($orders, 0, 6);
$updated = $catalog['updated'] ?? '';
?>
<div class="stats">
  <a class="stat hot" href="/admin/orders?status=new"><span><?= $newOrders ?></span>новых заказов</a>
  <a class="stat" href="/admin/orders"><span><?= count($orders) ?></span>заказов всего</a>
  <a class="stat" href="/admin/customers"><span><?= count($customers) ?></span>клиентов</a>
  <a class="stat" href="/admin/products"><span><?= count($products) ?></span>товаров</a>
  <a class="stat" href="/admin/categories"><span><?= count($cats) ?></span>категории</a>
  <a class="stat" href="/admin/designs"><span><?= count($designs) ?></span>дизайнов клиентов</a>
</div>

<div class="card">
  <div class="card-hd">
    <h2>Последние заказы</h2>
    <a href="/admin/orders">Все →</a>
  </div>
  <?php if ($lastOrders): ?>
  <table class="grid">
    <thead><tr><th>Заказ</th><th>Клиент</th><th class="hide-s">Что заказали</th><th class="hide-s">На дату</th><th>Статус</th></tr></thead>
    <tbody>
    <?php foreach ($lastOrders as $o): ?>
      <tr>
        <td><a href="/admin/orders?id=<?= e($o['id']) ?>"><b><?= e($o['id']) ?></b></a><small><?= date('d.m.Y H:i', $o['created']) ?></small></td>
        <td><b><?= e($o['name']) ?></b><small><?= e($o['phone']) ?></small></td>
        <td class="hide-s"><?= e($o['product'] ?: 'Конструктор') ?><small><?= e(trim($o['size'] . ' · ' . $o['filling'], ' ·')) ?></small></td>
        <td class="hide-s"><?= e($o['date'] ?: '—') ?><small><?= e($o['time']) ?></small></td>
        <td><span class="st <?= e($o['status'] ?? 'new') ?>"><?= ORDER_STATUSES[$o['status'] ?? 'new'] ?? e($o['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="pad muted">Заказов пока нет. Каждый заказ с сайта сохраняется здесь — с составом, датой доставки, адресом и контактами, ещё до перехода в WhatsApp.</div>
  <?php endif; ?>
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
        <small>Фото, цена, категория и SEO</small>
      </a>
      <a class="qbtn" href="/admin/orders?status=new">
        <b>Новые заказы</b>
        <small><?= $newOrders ?: 'Пока ни одного' ?><?= $newOrders ? ' ждут подтверждения' : '' ?></small>
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
