<?php
$q    = trim((string)($_GET['q'] ?? ''));
$open = preg_replace('/\D+/', '', (string)($_GET['phone'] ?? ''));

$rows = array_filter($customers, function ($c) use ($q) {
    if ($q === '') return true;
    return mb_stripos($c['name'] . ' ' . $c['phone'], $q) !== false;
});
$one = $customers[$open] ?? null;

$repeat = count(array_filter($customers, fn($c) => $c['orders'] > 1));
$total  = array_sum(array_column($customers, 'sum'));
?>

<div class="stats">
  <div class="stat hot"><span><?= count($customers) ?></span>клиентов</div>
  <div class="stat"><span><?= $repeat ?></span>заказывали не раз</div>
  <div class="stat"><span><?= $total ? number_format($total, 0, '.', ' ') . ' ₼' : '—' ?></span>сумма заказов</div>
</div>

<?php if ($one): ?>
<div class="card">
  <div class="card-hd">
    <h2><?= e($one['name'] ?: 'Без имени') ?> · <?= e($one['phone']) ?></h2>
    <a href="/admin/customers">← Все клиенты</a>
  </div>
  <div class="pad">
    <div class="kv">
      <div><span>Заказов</span><b><?= $one['orders'] ?></b></div>
      <div><span>На сумму</span><b><?= $one['sum'] ? number_format($one['sum'], 0, '.', ' ') . ' ₼' : '—' ?></b></div>
      <div><span>Первый заказ</span><b><?= date('d.m.Y', $one['first']) ?></b></div>
      <div><span>Последний</span><b><?= date('d.m.Y', $one['last']) ?></b></div>
      <?php if ($one['address']): ?><div><span>Адрес</span><b><?= e($one['address']) ?></b></div><?php endif; ?>
      <div><span>Связаться</span><b>
        <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $one['phone'])) ?>"><?= e($one['phone']) ?></a>
        &nbsp;<a class="btn ghost sm" href="https://wa.me/<?= e(preg_replace('/\D/', '', $one['phone'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </b></div>
    </div>
  </div>
  <table class="grid">
    <thead><tr><th>Заказ</th><th>Что заказали</th><th class="hide-s">На дату</th><th>Статус</th></tr></thead>
    <tbody>
    <?php foreach ($one['items'] as $o): ?>
      <tr>
        <td><a href="/admin/orders?id=<?= e($o['id']) ?>"><b><?= e($o['id']) ?></b></a><small><?= date('d.m.Y H:i', $o['created']) ?></small></td>
        <td><?= e($o['product'] ?: 'Конструктор') ?><small><?= e(trim($o['size'] . ' · ' . $o['filling'], ' ·')) ?></small></td>
        <td class="hide-s"><?= e($o['date'] ?: '—') ?><small><?= e($o['time']) ?></small></td>
        <td><span class="st <?= e($o['status'] ?? 'new') ?>"><?= ORDER_STATUSES[$o['status'] ?? 'new'] ?? e($o['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <h2>База клиентов</h2>
    <form class="search" method="get">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Имя или телефон…">
    </form>
  </div>
  <table class="grid">
    <thead><tr><th>Клиент</th><th class="hide-s">Телефон</th><th>Заказов</th><th class="hide-s">На сумму</th><th class="hide-s">Последний</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $key => $c): ?>
      <tr>
        <td>
          <a href="/admin/customers?phone=<?= e($key) ?>"><b><?= e($c['name'] ?: 'Без имени') ?></b></a>
          <small class="show-s"><?= e($c['phone']) ?></small>
        </td>
        <td class="hide-s"><?= e($c['phone']) ?></td>
        <td><?= $c['orders'] ?><?= $c['orders'] > 1 ? ' <span class="pill set">постоянный</span>' : '' ?></td>
        <td class="hide-s"><?= $c['sum'] ? number_format($c['sum'], 0, '.', ' ') . ' ₼' : '—' ?></td>
        <td class="hide-s"><?= date('d.m.Y', $c['last']) ?></td>
        <td class="right">
          <a class="btn ghost sm" href="/admin/customers?phone=<?= e($key) ?>">Открыть</a>
          <a class="btn ghost sm" href="https://wa.me/<?= e($key) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="pad muted">
        <?= $customers ? 'Ничего не найдено.' : 'Клиенты появятся здесь автоматически: каждый оформленный на сайте заказ добавляет контакт и историю покупок.' ?>
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
