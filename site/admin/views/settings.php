<div class="card">
  <div class="card-hd"><h2>Контакты</h2><span class="muted">видны на всех страницах сайта</span></div>
  <form method="post" class="pad">
    <input type="hidden" name="action" value="settings">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <div class="pair">
      <div>
        <label for="phone_display">Телефон (как показывать)</label>
        <input type="text" id="phone_display" name="phone_display" value="<?= e($SETTINGS['phone_display']) ?>">

        <label for="phone_tel">Телефон для звонка</label>
        <input type="text" id="phone_tel" name="phone_tel" value="<?= e($SETTINGS['phone_tel']) ?>">
        <p class="hint">Без пробелов, с кодом страны: +994552156343</p>

        <label for="wa_number">Номер WhatsApp</label>
        <input type="text" id="wa_number" name="wa_number" value="<?= e($SETTINGS['wa_number']) ?>">
        <p class="hint">Только цифры: 994552156343 — на него уходят все заказы</p>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" value="<?= e($SETTINGS['email']) ?>">
      </div>
      <div>
        <label for="ig_url">Instagram — ссылка</label>
        <input type="text" id="ig_url" name="ig_url" value="<?= e($SETTINGS['ig_url']) ?>">

        <label for="ig_handle">Instagram — имя</label>
        <input type="text" id="ig_handle" name="ig_handle" value="<?= e($SETTINGS['ig_handle']) ?>">

        <label for="fb_url">Facebook</label>
        <input type="text" id="fb_url" name="fb_url" value="<?= e($SETTINGS['fb_url']) ?>">

        <label for="canon_host">Адрес сайта</label>
        <input type="text" id="canon_host" name="canon_host" value="<?= e($SETTINGS['canon_host']) ?>">
        <p class="hint">Меняйте, только если сайт переезжает на другой домен: от него зависят ссылки в заказах и данные для поисковиков.</p>
      </div>
    </div>

    <h3>Точка на карте</h3>
    <div class="pair">
      <div>
        <label for="map_lat">Широта</label>
        <input type="text" id="map_lat" name="map_lat" value="<?= e($SETTINGS['map_lat']) ?>">
        <label for="map_lng">Долгота</label>
        <input type="text" id="map_lng" name="map_lng" value="<?= e($SETTINGS['map_lng']) ?>">
        <p class="hint">Координаты кондитерской — по ним строится карта и маршруты на странице «Контакты».</p>
      </div>
      <div>
        <label for="map_url">Ссылка на Google Maps</label>
        <textarea id="map_url" name="map_url" rows="3"><?= e($SETTINGS['map_url']) ?></textarea>
        <p class="hint"><a href="<?= e($SETTINGS['map_url']) ?>" target="_blank" rel="noopener">Проверить ссылку ↗</a></p>
      </div>
    </div>

    <div class="form-foot"><button>Сохранить</button></div>
  </form>
</div>
