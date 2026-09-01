<div class="auth-card">
  <div class="auth-logo">
    <img src="/assets/logo.svg" alt="Vanilla Cake" width="170" height="38">
  </div>
  <h1><?= $hasPass ? 'Вход в панель' : 'Первый вход' ?></h1>
  <p class="muted"><?= $hasPass ? 'Введите пароль администратора' : 'Придумайте пароль — минимум 8 символов' ?></p>

  <?php if ($err): ?><div class="flash bad"><?= e($err) ?></div><?php endif; ?>
  <?php if ($note): ?><div class="flash <?= e($note['kind']) ?>"><?= e($note['message']) ?></div><?php endif; ?>

  <?php if ($hasPass): ?>
  <form method="post">
    <input type="hidden" name="action" value="login">
    <label for="pass">Пароль</label>
    <input type="password" name="pass" id="pass" autofocus required>
    <button class="block">Войти</button>
  </form>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="action" value="setpass">
    <label for="p1">Новый пароль</label>
    <input type="password" name="p1" id="p1" minlength="8" autofocus required>
    <label for="p2">Повторите пароль</label>
    <input type="password" name="p2" id="p2" minlength="8" required>
    <button class="block">Создать пароль</button>
  </form>
  <?php endif; ?>
</div>
