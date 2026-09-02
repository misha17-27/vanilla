<div class="auth-card">
  <div class="auth-logo">
    <img src="/assets/logo.svg" alt="Vanilla Cake" width="170" height="38">
  </div>
  <h1><?= $hasPass ? 'Вход в панель' : 'Первый вход' ?></h1>
  <p class="muted"><?= $hasPass ? 'Имя или e-mail и пароль' : 'Создайте администратора панели' ?></p>

  <?php if ($err): ?><div class="flash bad"><?= e($err) ?></div><?php endif; ?>
  <?php if ($note): ?><div class="flash <?= e($note['kind']) ?>"><?= e($note['message']) ?></div><?php endif; ?>

  <?php if ($hasPass): ?>
  <form method="post">
    <input type="hidden" name="action" value="login">
    <label for="login">Имя или e-mail</label>
    <input type="text" name="login" id="login" autocomplete="username" autofocus required>
    <label for="pass">Пароль</label>
    <input type="password" name="pass" id="pass" autocomplete="current-password" required>
    <?= turnstile_widget('auth-cf') ?>
    <button class="block">Войти</button>
  </form>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="action" value="setpass">
    <label for="name">Имя</label>
    <input type="text" name="name" id="name" placeholder="Как показывать в панели" autofocus>
    <label for="email">E-mail</label>
    <input type="email" name="email" id="email" autocomplete="username" required>
    <label for="p1">Пароль</label>
    <input type="password" name="p1" id="p1" minlength="8" autocomplete="new-password" required>
    <label for="p2">Повторите пароль</label>
    <input type="password" name="p2" id="p2" minlength="8" required>
    <button class="block">Создать пользователя</button>
  </form>
  <?php endif; ?>
</div>
