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

<div class="card narrow">
  <div class="card-hd"><h2>Безопасность</h2></div>
  <div class="pad seo-check">
    <div class="chk ok"><b>Вход защищён</b><span>Пароль хранится в зашифрованном виде. После 5 неудачных попыток вход блокируется на 15 минут.</span></div>
    <div class="chk ok"><b>Загрузки проверяются</b><span>Файлы клиентов принимаются только как изображения, пересохраняются на сервере и не могут содержать программный код.</span></div>
    <div class="chk ok"><b>Резервные копии каталога</b><span>Перед каждым сохранением создаётся копия — хранятся последние 20.</span></div>
  </div>
</div>
