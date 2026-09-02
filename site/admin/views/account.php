<?php $meRow = me(); ?>
<div class="card narrow">
  <div class="card-hd"><h2>Профиль</h2></div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="profile">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <label for="pname">Имя</label>
    <input type="text" id="pname" name="name" value="<?= e($meRow['name'] ?? '') ?>" required>
    <label>E-mail (логин)</label>
    <input type="text" value="<?= e($meRow['email'] ?? '') ?>" readonly>
    <p class="hint">Роль: <?= e(ROLES[$meRow['role'] ?? 'manager'] ?? '') ?>. E-mail меняет администратор в разделе «Пользователи».</p>
    <div class="form-foot"><button>Сохранить</button></div>
  </form>
</div>

<div class="card narrow">
  <div class="card-hd"><h2>Смена пароля</h2></div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="chpass">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <label for="cur">Текущий пароль</label>
    <input type="password" id="cur" name="cur" required>
    <label for="np1">Новый пароль</label>
    <input type="password" id="np1" name="p1" minlength="8" required>
    <label for="np2">Повторите новый пароль</label>
    <input type="password" id="np2" name="p2" minlength="8" required>
    <div class="form-foot"><button>Сменить пароль</button></div>
  </form>
</div>

<?php if (is_admin()):
$checks = [];
$phpOk = version_compare(PHP_VERSION, '8.1', '>=');
$checks[] = [$phpOk, 'PHP ' . PHP_VERSION, $phpOk ? 'Версия подходит.' : 'Нужен PHP 8.1 или новее: cPanel → MultiPHP Manager.'];
$gd = extension_loaded('gd');
$checks[] = [$gd, 'Обработка картинок (GD)', $gd ? 'Фото товаров кадрируются и пересохраняются на сервере.' : 'Расширение GD выключено — загрузка фото и конструктор работать не будут.'];
$writable = is_writable(__DIR__ . '/../../data') && is_writable(__DIR__ . '/../../uploads');
$checks[] = [$writable, 'Папки data и uploads', $writable ? 'Доступны для записи — заказы и загрузки сохраняются.' : 'Нет прав на запись: chmod 755 для data и uploads.'];
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);
$checks[] = [$https || $local, 'HTTPS', $https ? 'Сайт открывается по защищённому соединению.' : ($local ? 'Локальный запуск — проверка не нужна.' : 'Включите SSL в cPanel и редирект на https.')];
$host = parse_url(CANON_HOST, PHP_URL_HOST);
$hostOk = $local || ($host && str_contains((string)($_SERVER['HTTP_HOST'] ?? ''), $host));
$checks[] = [$hostOk, 'Основной адрес: ' . CANON_HOST, $hostOk ? 'Совпадает с доменом — ссылки в заказах и разметке верные.' : 'Домен не совпадает с настройкой. Поправьте в «Контакты и карта».'];
$rew = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules(), true) : true;
$checks[] = [$rew, 'Адреса страниц', $rew ? 'Правила .htaccess работают — адреса как на старом сайте.' : 'mod_rewrite не найден — страницы будут отдавать 404.'];
$bad = count(array_filter($checks, fn($c) => !$c[0]));
?>
<div class="card narrow">
  <div class="card-hd">
    <h2>Сервер</h2>
    <span class="muted"><?= $bad ? $bad . ' требует внимания' : 'всё в порядке' ?></span>
  </div>
  <div class="pad seo-check">
    <?php foreach ($checks as [$ok, $title, $note]): ?>
    <div class="chk <?= $ok ? 'ok' : 'warn' ?>"><b><?= e($title) ?></b><span><?= e($note) ?></span></div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="card narrow">
  <div class="card-hd"><h2>Безопасность</h2></div>
  <div class="pad seo-check">
    <div class="chk ok"><b>Вход защищён</b><span>Пароль хранится в зашифрованном виде. После 5 неудачных попыток вход блокируется на 15 минут.</span></div>
    <div class="chk ok"><b>Загрузки проверяются</b><span>Файлы клиентов принимаются только как изображения, пересохраняются на сервере и не могут содержать программный код.</span></div>
    <div class="chk ok"><b>Резервные копии каталога</b><span>Перед каждым сохранением создаётся копия — хранятся последние 20.</span></div>
  </div>
</div>
