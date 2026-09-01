<?php
$openId = (string)($_GET['id'] ?? '');
$filter = (string)($_GET['status'] ?? '');
$q      = trim((string)($_GET['q'] ?? ''));

$one = null;
foreach ($orders as $o) if ($o['id'] === $openId) { $one = $o; break; }

$rows = array_filter($orders, function ($o) use ($filter, $q) {
    if ($filter !== '' && ($o['status'] ?? 'new') !== $filter) return false;
    if ($q === '') return true;
    $hay = $o['id'] . ' ' . $o['name'] . ' ' . $o['phone'] . ' ' . $o['product'];
    return mb_stripos($hay, $q) !== false;
});

$counts = ['' => count($orders)];
foreach (ORDER_STATUSES as $k => $v) $counts[$k] = 0;
foreach ($orders as $o) { $st = $o['status'] ?? 'new'; if (isset($counts[$st])) $counts[$st]++; }

function order_sum(array $o): string { return trim((string)($o['price'] ?? '')); }
function order_when(int $ts): string { return date('d.m.Y H:i', $ts); }
?>

<?php if ($one): ?>
<?php
$fields = [
    'Товар'     => $one['product'],
    'Размер'    => $one['size'],
    'Бисквит'   => $one['sponge'],
    'Начинка'   => $one['filling'],
    'Надпись'   => $one['lettering'],
    'Дата'      => $one['date'],
    'Время'     => $one['time'],
    'Доставка'  => $one['delivery'],
    'Адрес'     => $one['address'],
    'Получатель'=> $one['recipient'],
];
?>
<div class="card">
  <div class="card-hd">
    <h2>Заказ <?= e($one['id']) ?> <span class="st <?= e($one['status']) ?>"><?= ORDER_STATUSES[$one['status']] ?? e($one['status']) ?></span></h2>
    <a href="/admin/orders">← Все заказы</a>
  </div>
  <div class="pad ordwrap">
    <div>
      <div class="kv">
        <div><span>Оформлен</span><b><?= order_when($one['created']) ?></b></div>
        <div><span>Имя</span><b><?= e($one['name']) ?></b></div>
        <div><span>Телефон</span><b><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $one['phone'])) ?>"><?= e($one['phone']) ?></a>
          &nbsp;<a class="btn ghost sm" href="https://wa.me/<?= e(preg_replace('/\D/', '', $one['phone'])) ?>" target="_blank" rel="noopener">WhatsApp</a></b></div>
        <?php foreach ($fields as $lbl => $val): if (trim((string)$val) === '') continue; ?>
        <div><span><?= e($lbl) ?></span><b><?= e($val) ?></b></div>
        <?php endforeach; ?>
        <?php if (!empty($one['point'])): ?>
        <div><span>Точка на карте</span><b><a href="https://maps.google.com/?q=<?= e($one['point']) ?>" target="_blank" rel="noopener"><?= e($one['point']) ?> ↗</a></b></div>
        <?php endif; ?>
        <?php if (!empty($one['url'])): ?>
        <div><span>Страница</span><b><a href="<?= e($one['url']) ?>" target="_blank" rel="noopener">Открыть ↗</a></b></div>
        <?php endif; ?>
        <?php foreach (($one['extras'] ?? []) as $x): ?>
        <div><span>Доп.</span><b><?= e($x) ?></b></div>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($one['photos'])): ?>
      <div class="gal" style="margin-top:18px">
        <?php foreach ($one['photos'] as $ph): ?>
        <a class="gal-item" href="<?= e($ph) ?>" target="_blank" rel="noopener"><img loading="lazy" src="<?= e($ph) ?>" alt=""><small>макет / фото клиента</small></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="ordside">
      <form method="post">
        <input type="hidden" name="action" value="order_status">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="id" value="<?= e($one['id']) ?>">
        <input type="hidden" name="back" value="card">
        <label for="st">Статус</label>
        <select name="status" id="st">
          <?php foreach (ORDER_STATUSES as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($one['status'] ?? 'new') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn block">Сохранить статус</button>
      </form>

      <form method="post" style="margin-top:18px">
        <input type="hidden" name="action" value="order_note">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="id" value="<?= e($one['id']) ?>">
        <label for="note">Комментарий</label>
        <textarea name="note" id="note" rows="4" placeholder="Заметка для себя…"><?= e($one['note'] ?? '') ?></textarea>
        <button class="btn ghost block">Сохранить комментарий</button>
      </form>

      <form method="post" style="margin-top:18px">
        <input type="hidden" name="action" value="order_delete">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="id" value="<?= e($one['id']) ?>">
        <button class="btn danger block" data-confirm="Удалить заказ <?= e($one['id']) ?>?">Удалить заказ</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <div class="filters">
      <a href="/admin/orders" class="chip <?= $filter === '' ? 'on' : '' ?>">Все <i><?= $counts[''] ?></i></a>
      <?php foreach (ORDER_STATUSES as $k => $v): ?>
      <a href="/admin/orders?status=<?= $k ?>" class="chip <?= $filter === $k ? 'on' : '' ?>"><?= $v ?> <i><?= $counts[$k] ?></i></a>
      <?php endforeach; ?>
    </div>
    <form class="search" method="get">
      <?php if ($filter): ?><input type="hidden" name="status" value="<?= e($filter) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Номер, имя, телефон…">
    </form>
  </div>

  <table class="grid">
    <thead><tr><th>Заказ</th><th>Клиент</th><th class="hide-s">Что заказали</th><th class="hide-s">Когда</th><th>Статус</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $o): ?>
      <tr>
        <td>
          <a href="/admin/orders?id=<?= e($o['id']) ?>"><b><?= e($o['id']) ?></b></a>
          <small><?= order_when($o['created']) ?></small>
        </td>
        <td>
          <b><?= e($o['name']) ?></b>
          <small><?= e($o['phone']) ?></small>
        </td>
        <td class="hide-s">
          <?= e($o['product'] ?: 'Конструктор') ?>
          <small><?= e(trim($o['size'] . ' · ' . $o['filling'], ' ·')) ?></small>
        </td>
        <td class="hide-s">
          <?= e($o['date'] ?: '—') ?>
          <small><?= e($o['time'] ?: $o['delivery']) ?></small>
        </td>
        <td>
          <span class="st <?= e($o['status'] ?? 'new') ?>"><?= ORDER_STATUSES[$o['status'] ?? 'new'] ?? e($o['status']) ?></span>
          <small><?= e(order_sum($o)) ?></small>
        </td>
        <td class="right">
          <a class="btn ghost sm" href="/admin/orders?id=<?= e($o['id']) ?>">Открыть</a>
          <a class="btn ghost sm" href="https://wa.me/<?= e(preg_replace('/\D/', '', $o['phone'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="pad muted">
        <?= $orders ? 'Ничего не найдено.' : 'Заказов пока нет. Здесь появится каждый заказ, оформленный через сайт — с составом, датой, адресом и контактами, ещё до перехода в WhatsApp.' ?>
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
