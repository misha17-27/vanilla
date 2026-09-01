<?php
$meMail = mb_strtolower((string)($_SESSION['user'] ?? ''));
$admins = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'admin' && !empty($u['active'])));
?>

<div class="card">
  <div class="card-hd">
    <h2>Добавить пользователя</h2>
    <span class="muted">«Администратор» — всё. «Менеджер» — заказы, клиенты и каталог.</span>
  </div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="user_add">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <div class="pair">
      <div>
        <label for="u-name">Имя</label>
        <input type="text" name="name" id="u-name" placeholder="Как показывать в панели">

        <label for="u-pass">Пароль <span class="muted">(минимум 8 символов)</span></label>
        <input type="password" name="pass" id="u-pass" minlength="8" autocomplete="new-password" required>
      </div>
      <div>
        <label for="u-email">E-mail <span class="muted">(это и есть логин)</span></label>
        <input type="email" name="email" id="u-email" required>

        <label for="u-role">Роль</label>
        <select name="role" id="u-role">
          <?php foreach (ROLES as $k => $v): ?>
          <option value="<?= $k ?>" <?= $k === 'manager' ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-foot"><button>Добавить</button></div>
  </form>
</div>

<div class="card">
  <div class="card-hd">
    <h2><?= count($users) ?> пользовател<?= count($users) === 1 ? 'ь' : 'и' ?></h2>
    <span class="muted">поле пароля оставьте пустым, если менять не нужно</span>
  </div>
  <form method="post">
    <input type="hidden" name="action" value="users_save">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <table class="grid">
      <thead><tr><th>Имя</th><th class="hide-s">E-mail</th><th>Роль</th><th class="hide-s">Новый пароль</th><th>Доступ</th><th class="hide-s">Вход</th><th class="right"></th></tr></thead>
      <tbody>
      <?php foreach ($users as $email => $u): $isMe = $email === $meMail; ?>
        <tr>
          <td>
            <input type="text" name="u[<?= e($email) ?>][name]" value="<?= e($u['name']) ?>" class="in-cell">
            <?php if ($isMe): ?><small class="me-tag">это вы</small><?php endif; ?>
            <small class="show-s"><?= e($u['email']) ?></small>
          </td>
          <td class="hide-s"><?= e($u['email']) ?></td>
          <td>
            <select name="u[<?= e($email) ?>][role]" class="in-cell">
              <?php foreach (ROLES as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($u['role'] ?? '') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="hide-s"><input type="password" name="u[<?= e($email) ?>][pass]" class="in-cell" placeholder="—" autocomplete="new-password"></td>
          <td>
            <label class="sw">
              <input type="checkbox" name="u[<?= e($email) ?>][active]" value="1" <?= !empty($u['active']) ? 'checked' : '' ?> <?= $isMe ? 'disabled' : '' ?>>
              <span></span>
            </label>
            <?php if ($isMe): ?><input type="hidden" name="u[<?= e($email) ?>][active]" value="1"><?php endif; ?>
          </td>
          <td class="hide-s"><?= !empty($u['last']) ? date('d.m.Y H:i', $u['last']) : '<span class="muted">ни разу</span>' ?></td>
          <td class="right">
            <?php if (!$isMe): ?>
            <button class="btn danger sm ico" form="del-<?= e(md5($email)) ?>" title="Удалить"
                    data-confirm="Удалить пользователя «<?= e($u['name']) ?>»?"><?= icon('trash') ?><span>Удалить</span></button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="pad"><button>Сохранить изменения</button></div>
  </form>
</div>

<?php foreach ($users as $email => $u): if ($email === $meMail) continue; ?>
<form method="post" id="del-<?= e(md5($email)) ?>" class="inline">
  <input type="hidden" name="action" value="user_delete">
  <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
  <input type="hidden" name="email" value="<?= e($email) ?>">
</form>
<?php endforeach; ?>

<div class="card">
  <div class="card-hd"><h2>Как это работает</h2></div>
  <div class="pad seo-check">
    <div class="chk ok"><b>Вход по e-mail</b><span>Логин — это e-mail пользователя. Пароли хранятся необратимым хэшем, после 5 неудачных попыток вход блокируется на 15 минут.</span></div>
    <div class="chk ok"><b>Менеджер</b><span>Видит заказы, клиентов, товары, категории и дизайны. Настройки, SEO, страницы и пользователи — только у администратора.</span></div>
    <div class="chk <?= $admins > 1 ? 'ok' : 'warn' ?>">
      <b>Администраторов: <?= $admins ?></b>
      <span><?= $admins > 1 ? 'Доступ не потеряется, если один выйдет из строя.' : 'Единственного администратора нельзя удалить, отключить или понизить. Заведите второго на всякий случай.' ?></span>
    </div>
  </div>
</div>
