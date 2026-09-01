<?php
$LANG_NAMES = ['ru' => 'RU', 'az' => 'AZ', 'en' => 'EN'];
$editKey = (string)($_GET['edit'] ?? '');
$l       = (string)($_GET['l'] ?? 'ru');
if (!isset($LANG_NAMES[$l])) $l = 'ru';
$page    = $PAGEMAP[$editKey] ?? null;

$seoData = load_seo();
$texts   = load_texts();
$defs    = ['ru' => require __DIR__ . '/../../lang/ru.php'];

// Человеческое имя поля выводим по самому ключу — значение всё равно видно в поле
function field_label(string $k): string
{
    static $exact = [
        'hero_h' => 'Заголовок', 'hero_lead' => 'Подзаголовок', 'hero_eyebrow' => 'Надпись сверху',
        'hero_trust' => 'Строка доверия', 'hero_cta2' => 'Кнопка', 'hours' => 'Режим работы',
        'hours_full' => 'Режим работы', 'pcs' => 'Сокращение «шт»', 'req_mark' => 'Пометка обязательного поля',
        'nf_text' => 'Текст «торт не найден»', 'fl_choose' => 'Подпись', 'chat_tag' => 'Надпись сверху',
        'chat_online' => 'Метка «онлайн»', 'rev_count' => 'Подпись под числом', 'rev_empty' => 'Если отзывов нет',
        'breadcrumb_home' => 'Хлебные крошки', 'contact_addr_v' => 'Адрес', 'f_other' => 'Галочка «другой получатель»',
        'ord_confirm' => 'Примечание под кнопкой', 'ord_send' => 'Кнопка отправки', 'k_del' => 'Кнопка «удалить»',
        'k_add_text' => 'Кнопка «добавить надпись»', 'k_img_none' => 'Кнопка «очистить»',
        'k_custom_name' => 'Название торта в заказе', 'k_wa_intro' => 'Первая строка сообщения',
        'f_text' => 'Подпись поля надписи', 'pd_order' => 'Кнопка заказа', 'up_remove' => 'Кнопка «удалить фото»',
        'up_loading' => 'Сообщение при загрузке', 'up_ok' => 'Сообщение об успехе',
    ];
    if (isset($exact[$k])) return $exact[$k];

    // пронумерованные блоки: occ5, stat2, f4q, u1t, hs2_d, chat_s3t, about_full_p2
    static $numbered = ['occ' => 'Повод', 'cb' => 'Пункт', 'stat' => 'Цифра', 'fld' => 'Описание бисквита'];
    if (preg_match('/^([a-z_]+?)(\d+)$/', $k, $m) && isset($numbered[$m[1]])) return $numbered[$m[1]] . ' ' . $m[2];
    if (preg_match('/^f(\d+)([qa])$/', $k, $m))     return ($m[2] === 'q' ? 'Вопрос ' : 'Ответ ') . $m[1];
    if (preg_match('/p(\d+)$/', $k, $m))            return 'Абзац ' . $m[1];
    if (preg_match('/(\d+)_?([td])$/', $k, $m))     return ($m[2] === 't' ? 'Заголовок ' : 'Описание ') . $m[1];

    foreach ([
        '_items' => 'Список', '_ph' => 'Подсказка в поле', '_btn' => 'Кнопка', '_note' => 'Примечание',
        '_lead' => 'Подзаголовок', '_eyebrow' => 'Надпись сверху', '_hint' => 'Подсказка',
        '_title' => 'Заголовок', '_desc' => 'Описание', '_h' => 'Заголовок', '_t' => 'Заголовок',
        '_d' => 'Описание', '_s' => 'Подпись', '_w' => 'Название', '_p' => 'Порции', '_c' => 'Цена',
        '_q' => 'Вопрос', '_a' => 'Ответ', '_n' => 'Название',
    ] as $suf => $name) {
        if (str_ends_with($k, $suf)) return $name;
    }
    foreach ([
        'nav_' => 'Пункт меню', 'footer_' => 'Заголовок колонки', 'sizes_opt_' => 'Размеры и цены',
        'pd_w_' => 'Описание под фото', 'pd_' => 'Текст вкладки', 'tab_' => 'Название вкладки',
        'dl_' => 'Вариант получения', 'opt_' => 'Подпись поля', 'f_' => 'Подпись поля',
        'map_' => 'Подпись', 'val_' => 'Сообщение об ошибке', 'up_e_' => 'Сообщение об ошибке',
        'up_' => 'Подпись', 'wa_' => 'Подпись в сообщении', 'btn_' => 'Кнопка', 'p_' => 'Название',
        'contact_' => 'Подпись', 'k_' => 'Подпись', 'about_' => 'Текст', 'ig_' => 'Подпись',
    ] as $pre => $name) {
        if (str_starts_with($k, $pre)) return $name;
    }
    return $k;
}

// Заполненность страницы на выбранном языке
function page_filled(array $page, array $def): bool
{
    foreach ($page['groups'] as $keys) {
        foreach ($keys as $k) {
            $v = $def[$k] ?? '';
            if ($v === '' || $v === []) return false;
        }
    }
    return true;
}
?>

<?php if ($page): ?>
<?php
$def = $defs[$l] ?? ($defs[$l] = require __DIR__ . '/../../lang/' . $l . '.php');
$own = $texts[$l] ?? [];
$edited = 0;
foreach ($page['groups'] as $keys) foreach ($keys as $k) if (isset($own[$k])) $edited++;
?>
<div class="card">
  <div class="card-hd">
    <h2><?= e($page['label']) ?></h2>
    <div class="langsw">
      <?php foreach ($LANG_NAMES as $code => $name): ?>
      <a class="<?= $l === $code ? 'on' : '' ?>" href="/admin/pages?edit=<?= e($editKey) ?>&l=<?= $code ?>"><?= $name ?></a>
      <?php endforeach; ?>
    </div>
    <a href="/admin/pages">← Все страницы</a>
  </div>

  <form method="post" class="pad">
    <input type="hidden" name="action" value="page_save">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="page" value="<?= e($editKey) ?>">
    <input type="hidden" name="l" value="<?= e($l) ?>">

    <?php if ($page['url']): ?>
    <div class="urlrow">
      <div>
        <label>Адрес страницы</label>
        <input type="text" value="<?= e($page['url']) ?>" readonly>
        <p class="hint">Адрес не меняется — на нём держатся позиции в поиске.</p>
      </div>
      <a class="btn ghost" href="<?= e($page['url']) ?>" target="_blank" rel="noopener">Открыть ↗</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($page['seo'])): ?>
    <div class="seo-block">
      <div class="seo-head"><b>SEO</b><span class="muted">одно на все языки — как было на vanilla.az</span></div>
      <label for="s-t">Заголовок (title)</label>
      <input type="text" id="s-t" name="seo_title" maxlength="120" value="<?= e($seoData[$page['seo']]['title'] ?? '') ?>">
      <label for="s-d">Описание (description)</label>
      <textarea id="s-d" name="seo_desc" rows="2" maxlength="320"><?= e($seoData[$page['seo']]['desc'] ?? '') ?></textarea>
      <p class="hint">Если оставить пусто, подставится заголовок из переводов.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($page['groups'] as $group => $keys): ?>
    <div class="txgroup">
      <h3><?= e($group) ?></h3>
      <div class="txfields">
        <?php foreach ($keys as $k):
            $default = $def[$k] ?? '';
            $value   = $own[$k] ?? $default;
            $kind    = field_kind($default);
            $changed = isset($own[$k]);
            if ($kind === 'list')       $shown = implode("\n", (array)$value);
            elseif ($kind === 'pairs')  { $shown = ''; foreach ((array)$value as $row) $shown .= $row[0] . ' | ' . $row[1] . "\n"; $shown = rtrim($shown); }
            else                        $shown = (string)$value;
        ?>
        <div class="txf <?= $kind === 'line' ? '' : 'wide' ?>">
          <label for="f-<?= e($k) ?>">
            <?= e(field_label($k)) ?>
            <code><?= e($k) ?></code>
            <?php if ($changed): ?><i class="mark">изменено</i><?php endif; ?>
          </label>
          <?php if ($kind === 'line'): ?>
          <input type="text" id="f-<?= e($k) ?>" name="tx[<?= e($k) ?>]" value="<?= e($shown) ?>">
          <?php else: ?>
          <textarea id="f-<?= e($k) ?>" name="tx[<?= e($k) ?>]" rows="<?= $kind === 'area' ? 3 : max(3, substr_count($shown, "\n") + 1) ?>"><?= e($shown) ?></textarea>
          <?php endif; ?>
          <?php if ($kind === 'list'): ?><p class="hint">По одному пункту в строке.</p><?php endif; ?>
          <?php if ($kind === 'pairs'): ?><p class="hint">Строка = «название | цена». Эти размеры видит клиент в форме заказа.</p><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="form-foot">
      <button>Сохранить</button>
      <a class="btn ghost" href="/admin/pages">Отмена</a>
      <span class="muted" style="margin-left:auto">
        <?= $edited ? 'Своих правок на этом языке: ' . $edited : 'Тексты как в переводе по умолчанию' ?>
      </span>
    </div>
  </form>

  <?php if ($edited): ?>
  <form method="post" class="pad hintline">
    <input type="hidden" name="action" value="page_reset">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="page" value="<?= e($editKey) ?>">
    <input type="hidden" name="l" value="<?= e($l) ?>">
    <button class="btn danger sm" data-confirm="Вернуть тексты этой страницы (<?= e($LANG_NAMES[$l]) ?>) к исходным?">Вернуть исходные тексты</button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-hd">
    <h2>Страницы сайта</h2>
    <span class="muted">адрес, тексты и SEO каждой страницы</span>
  </div>
  <table class="grid">
    <thead><tr><th>Страница</th><th class="hide-s">Адрес</th><th>Текст</th><th>SEO</th><th class="right"></th></tr></thead>
    <tbody>
    <?php foreach ($PAGEMAP as $key => $p): ?>
      <?php
      $fields = 0;
      $mine   = 0;
      foreach ($p['groups'] as $keys) {
          $fields += count($keys);
          foreach ($keys as $k) foreach (['ru', 'az', 'en'] as $lc) if (isset($texts[$lc][$k])) { $mine++; break; }
      }
      $hasSeo  = !empty($p['seo']) && trim($seoData[$p['seo']]['title'] ?? '') !== '';
      $hasText = page_filled($p, $defs['ru']);
      ?>
      <tr>
        <td>
          <a href="/admin/pages?edit=<?= e($key) ?>"><b><?= e($p['label']) ?></b></a>
          <small><?= $fields ?> текстовых полей<?= $mine ? ' · своих правок: ' . $mine : '' ?></small>
        </td>
        <td class="hide-s"><?= $p['url'] ? '<code>' . e($p['url']) . '</code>' : '<span class="muted">на всех страницах</span>' ?></td>
        <td><span class="dot <?= $hasText ? 'ok' : 'no' ?>"></span></td>
        <td><?= empty($p['seo']) ? '<span class="muted">—</span>' : '<span class="dot ' . ($hasSeo ? 'ok' : 'no') . '"></span>' ?></td>
        <td class="right">
          <a class="btn ghost sm" href="/admin/pages?edit=<?= e($key) ?>">Редактировать</a>
          <?php if ($p['url'] && !str_contains($p['url'], '…')): ?>
          <a class="btn ghost sm" href="<?= e($p['url']) ?>" target="_blank" rel="noopener">Открыть</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="pad hintline">Тексты правятся отдельно для каждого языка — переключатель RU / AZ / EN внутри страницы. Пустое поле означает «оставить как в переводе по умолчанию».</div>
</div>
