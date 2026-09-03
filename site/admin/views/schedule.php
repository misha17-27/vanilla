<?php
$sch = load_schedule();
$mon = (string)($_GET['m'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $mon)) $mon = date('Y-m');
$first = new DateTimeImmutable($mon . '-01');
$prev  = $first->modify('-1 month')->format('Y-m');
$next  = $first->modify('+1 month')->format('Y-m');
$today = new DateTimeImmutable('today');

$WD = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
$MONTHS = ['', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];

$closedAhead = count(array_filter($sch['closed'], fn($d) => $d >= $today->format('Y-m-d')));
?>

<div class="card">
  <div class="card-hd">
    <h2><?= e($MONTHS[(int)$first->format('n')]) ?> <?= $first->format('Y') ?></h2>
    <div class="calnav">
      <a class="btn ghost sm" href="/admin/schedule?m=<?= $prev ?>">←</a>
      <a class="btn ghost sm" href="/admin/schedule?m=<?= date('Y-m') ?>">Текущий месяц</a>
      <a class="btn ghost sm" href="/admin/schedule?m=<?= $next ?>">→</a>
    </div>
  </div>

  <form method="post" class="pad">
    <input type="hidden" name="action" value="schedule">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="month" value="<?= e($mon) ?>">
    <input type="hidden" name="closed" id="closed-days" value="">

    <p class="hint" style="margin:0 0 16px">Нажимайте на числа — отмеченные дни клиент не сможет выбрать в форме заказа. Постоянные выходные задаются ниже.</p>

    <div class="calx" id="calx">
      <?php foreach ($WD as $i => $w): ?><span class="calx-wd<?= $i >= 5 ? ' we' : '' ?>"><?= $w ?></span><?php endforeach; ?>
      <?php
      $start = ((int)$first->format('N')) - 1;
      for ($i = 0; $i < $start; $i++) echo '<span></span>';
      $days = (int)$first->format('t');
      for ($d = 1; $d <= $days; $d++):
          $date  = $first->modify('+' . ($d - 1) . ' day');
          $iso   = $date->format('Y-m-d');
          $wd    = ((int)$date->format('N')) % 7;          // 0 = воскресенье
          $weekly = in_array($wd, $sch['weekly'], true);
          $off   = in_array($iso, $sch['closed'], true);
          $past  = $iso < $today->format('Y-m-d');
      ?>
      <button type="button" class="calx-day<?= $off ? ' off' : '' ?><?= $weekly ? ' weekly' : '' ?><?= $past ? ' past' : '' ?><?= $iso === $today->format('Y-m-d') ? ' today' : '' ?>"
              data-date="<?= $iso ?>" <?= $weekly ? 'title="Постоянный выходной"' : '' ?>><?= $d ?></button>
      <?php endfor; ?>
    </div>

    <div class="calx-legend">
      <span><i class="lg off"></i> выходной</span>
      <span><i class="lg weekly"></i> постоянный выходной</span>
      <span><i class="lg work"></i> рабочий день</span>
    </div>

    <div class="form-foot"><button>Сохранить месяц</button></div>
  </form>
</div>

<div class="card narrow">
  <div class="card-hd"><h2>Постоянные выходные</h2></div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="schedule_weekly">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <div class="wdays">
      <?php foreach ([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 0 => 'Вс'] as $n => $w): ?>
      <label class="wday">
        <input type="checkbox" name="weekly[]" value="<?= $n ?>" <?= in_array($n, $sch['weekly'], true) ? 'checked' : '' ?>>
        <span><?= $w ?></span>
      </label>
      <?php endforeach; ?>
    </div>
    <p class="hint">Эти дни недели закрыты всегда — отдельно отмечать их в календаре не нужно.</p>
    <div class="form-foot"><button>Сохранить</button></div>
  </form>
</div>

<div class="card narrow">
  <div class="card-hd"><h2>Как это видит клиент</h2></div>
  <div class="pad seo-check">
    <div class="chk ok"><b>Форма заказа</b><span>В календаре выбора даты закрытые дни недоступны — ни на сайте, ни в конструкторе.</span></div>
    <div class="chk ok"><b>Ближайшая дата</b><span>Заказ принимается минимум за день: сегодня оформили — ближайший возможный день послезавтра.</span></div>
    <div class="chk <?= $closedAhead ? 'ok' : 'warn' ?>">
      <b>Отмечено впереди: <?= $closedAhead ?></b>
      <span><?= $closedAhead ? 'Эти дни клиент выбрать не сможет.' : 'Разовых выходных впереди нет — работают только постоянные.' ?></span>
    </div>
  </div>
</div>

<script>
(function () {
  var grid = document.getElementById('calx');
  var field = document.getElementById('closed-days');
  if (!grid || !field) return;
  function sync() {
    var on = [].slice.call(grid.querySelectorAll('.calx-day.off')).map(function (b) { return b.dataset.date; });
    field.value = on.join(',');
  }
  grid.addEventListener('click', function (e) {
    var b = e.target.closest('.calx-day');
    if (!b || b.classList.contains('weekly')) return;
    b.classList.toggle('off');
    sync();
  });
  sync();
})();
</script>
