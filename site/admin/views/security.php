<?php $sec = load_security(); $on = turnstile_on(); ?>

<div class="card narrow">
  <div class="card-hd">
    <h2>Cloudflare Turnstile (капча)</h2>
    <span class="st <?= $on ? 'done' : 'canceled' ?>"><?= $on ? 'включена' : 'выключена' ?></span>
  </div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="security">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <p class="hint" style="margin:0 0 14px">Защищает форму заказа на сайте и вход в эту панель от ботов. Живым посетителям обычно ничего не показывает.</p>

    <label for="ts-site">Site Key <span class="muted">(публичный)</span></label>
    <input type="text" id="ts-site" name="turnstile_site" value="<?= e($sec['turnstile_site']) ?>" placeholder="0x4AAAAAAA…">

    <label for="ts-secret">Secret Key <span class="muted">(секретный)</span></label>
    <input type="text" id="ts-secret" name="turnstile_secret" value="" placeholder="<?= $sec['turnstile_secret'] !== '' ? 'сохранён — оставьте пустым, чтобы не менять' : '0x4AAAAAAA…' ?>">
    <p class="hint">Чтобы выключить капчу — очистите оба поля и сохраните.</p>

    <div class="form-foot"><button>Сохранить</button></div>
  </form>
</div>

<div class="card narrow">
  <div class="card-hd"><h2>Где взять ключи</h2></div>
  <div class="pad">
    <ol class="steps">
      <li>Зайдите на <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener">dash.cloudflare.com</a> → раздел <b>Turnstile</b>.</li>
      <li>Нажмите <b>Add widget</b>.</li>
      <li>Domain — укажите <b><?= e(parse_url(CANON_HOST, PHP_URL_HOST) ?: 'vanilla.az') ?></b>.</li>
      <li>Widget Mode — <b>Managed</b>.</li>
      <li>Скопируйте Site Key и Secret Key в поля выше.</li>
    </ol>
  </div>
</div>

<div class="card narrow">
  <div class="card-hd"><h2>Что уже защищено</h2></div>
  <div class="pad seo-check">
    <div class="chk ok"><b>Пароли</b><span>Хранятся необратимым хэшем. Подобрать по файлу нельзя.</span></div>
    <div class="chk ok"><b>Вход в панель</b><span>После 5 неудачных попыток вход с этого адреса блокируется на 15 минут.</span></div>
    <div class="chk ok"><b>Все формы</b><span>Защищены токеном CSRF — отправить их с чужого сайта не получится.</span></div>
    <div class="chk ok"><b>Загрузки клиентов</b><span>Принимаются только картинки, пересохраняются на сервере, папка не исполняет код. Не больше 10 файлов в час с адреса.</span></div>
    <div class="chk ok"><b>Заказы</b><span>Поля очищаются от управляющих символов и обрезаются по длине. Не больше 20 заказов в час с адреса.</span></div>
    <div class="chk ok"><b>Служебные папки</b><span><code>data</code>, <code>includes</code>, <code>lang</code> и лимиты закрыты и через .htaccess, и в роутере.</span></div>
    <div class="chk <?= $on ? 'ok' : 'warn' ?>">
      <b>Капча</b>
      <span><?= $on ? 'Turnstile включена — боты не оформят заказ и не будут перебирать пароли.' : 'Не настроена. Пока держится на лимитах по адресу — для небольшого магазина этого обычно хватает.' ?></span>
    </div>
  </div>
</div>
