<div class="card">
  <div class="card-hd">
    <h2>Что присылают клиенты</h2>
    <span class="muted"><?= count($designs) ?> файлов</span>
  </div>
  <?php if ($designs): ?>
  <div class="gal pad big">
    <?php foreach ($designs as $d): ?>
    <div class="gal-item">
      <a href="/uploads/designs/<?= e($d['name']) ?>" target="_blank" rel="noopener">
        <img loading="lazy" src="/uploads/designs/<?= e($d['name']) ?>" alt="">
      </a>
      <small><?= date('d.m.Y H:i', $d['time']) ?> · <?= round($d['size'] / 1024) ?> КБ</small>
      <form method="post" class="inline">
        <input type="hidden" name="action" value="delete_design">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="file" value="<?= e($d['name']) ?>">
        <button class="btn danger sm" data-confirm="Удалить файл?">Удалить</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="pad muted">
    Пока пусто. Сюда попадают макеты из конструктора торта и фото, которые клиенты прикрепляют к заказу.
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-hd"><h2>Как это работает</h2></div>
  <div class="pad muted">
    Когда клиент собирает торт в конструкторе или прикрепляет своё фото, файл сохраняется здесь, а ссылка на него приходит вам в WhatsApp вместе с заказом.
    Файлы можно удалять — на уже отправленные заказы это не влияет, но ссылка в старом сообщении перестанет открываться.
  </div>
</div>
