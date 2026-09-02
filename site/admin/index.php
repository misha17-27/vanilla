<?php
// ===== Админ-панель Vanilla Cake =====

// Чего может не хватать на хостинге. Без этой проверки любая нехватка
// даёт пустую 500-ю страницу, по которой ничего не понять.
$need = [
    'mbstring' => 'работа с русским и азербайджанским текстом',
    'gd'       => 'кадрирование фото товаров и конструктор торта',
    'json'     => 'хранение каталога и заказов',
];
$missing = [];
foreach ($need as $ext => $why) if (!extension_loaded($ext)) $missing[$ext] = $why;
$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir) || !is_writable($dataDir)) $missing['__data'] = 'папка data должна быть доступна для записи (chmod 755)';
if (!is_writable(__DIR__ . '/../uploads')) $missing['__uploads'] = 'папка uploads должна быть доступна для записи (chmod 755)';
if (version_compare(PHP_VERSION, '8.1', '<')) $missing['__php'] = 'нужен PHP 8.1 или новее, сейчас ' . PHP_VERSION;

if ($missing) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Панель не может запуститься</title>'
       . '<style>body{font:15px/1.6 system-ui,sans-serif;max-width:680px;margin:60px auto;padding:0 20px;color:#2A4159}'
       . 'h1{font-size:22px;color:#16527F}li{margin:8px 0}code{background:#f2f4f7;padding:2px 6px;border-radius:5px}</style>'
       . '<h1>Панель не может запуститься</h1><p>На сервере не хватает вот этого:</p><ul>';
    foreach ($missing as $k => $why) {
        $name = str_starts_with($k, '__') ? '' : '<code>' . htmlspecialchars($k) . '</code> — ';
        echo '<li>', $name, htmlspecialchars($why), '</li>';
    }
    echo '</ul><p>Расширения включаются в cPanel → <b>Select PHP Version → Extensions</b>, '
       . 'версия PHP — в <b>MultiPHP Manager</b>, права на папки — в <b>File Manager</b>.</p>';
    exit;
}

require_once __DIR__ . '/../includes/config.php';

const ADMIN_PASS_FILE = __DIR__ . '/../data/admin.pass';
const USERS_FILE      = __DIR__ . '/../data/users.json';
const ROLES           = ['admin' => 'Администратор', 'manager' => 'Менеджер'];
// менеджеру доступны заказы, клиенты и каталог; настройки и пользователи — только администратору
const MANAGER_VIEWS   = ['dashboard', 'orders', 'customers', 'products', 'categories', 'designs', 'account'];
const BACKUP_DIR      = __DIR__ . '/../data/backups';
const PRODUCT_IMG_DIR = __DIR__ . '/../assets/img/products';
const THUMB_DIR       = __DIR__ . '/../assets/img/thumbs';
const DESIGN_DIR      = __DIR__ . '/../uploads/designs';
const ORDERS_FILE      = __DIR__ . '/../data/orders.json';
const ORDER_STATUSES  = ['new' => 'Новый', 'confirmed' => 'Подтверждён', 'done' => 'Выполнен', 'canceled' => 'Отменён'];
const CAT_PAGES       = [
    'own'   => 'Отдельная страница /bolme/…/',
    'bento' => 'Блоком в разделе «Бенто-торты»',
    'ctg'   => 'Блоком в разделе «Cake to go»',
];
const TEXTS_FILE_ADM  = __DIR__ . '/../data/texts.json';
const SEO_FILE        = __DIR__ . '/../data/seo.json';
$PAGEMAP = require __DIR__ . '/pagemap.php';
// страницы своих категорий добавляем в карту: у них правится SEO
foreach (own_categories() as $c) {
    $PAGEMAP['cat_' . $c['key']] = [
        'label'  => $c['name'],
        'url'    => cat_url($c),
        'seo'    => 'cat_' . $c['key'],
        'groups' => [],
    ];
}

// Ключи SEO по карте страниц — чтобы разделы «Страницы» и «SEO» не расходились
function seo_keys(): array
{
    global $PAGEMAP;
    $out = [];
    foreach ($PAGEMAP as $p) if (!empty($p['seo'])) $out[] = $p['seo'];
    return $out;
}

function load_seo(): array
{
    return json_decode((string)@file_get_contents(SEO_FILE), true) ?: [];
}

function save_seo(array $seo): void
{
    file_put_contents(SEO_FILE, json_encode($seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

function load_texts(): array
{
    return json_decode((string)@file_get_contents(TEXTS_FILE_ADM), true) ?: [];
}

// Тип поля определяем по значению из перевода: список, пары «название | цена» или обычный текст
function field_kind($default): string
{
    if (is_array($default)) return is_array($default[0] ?? null) ? 'pairs' : 'list';
    return (mb_strlen((string)$default) > 90 || str_contains((string)$default, '<')) ? 'area' : 'line';
}

// Из того, что ввели в форме, обратно в значение нужного типа
function field_value(string $raw, string $kind)
{
    $raw = str_replace(["\r\n", "\r"], "\n", trim($raw));
    if ($kind === 'list') {
        $out = [];
        foreach (explode("\n", $raw) as $line) { $line = trim($line); if ($line !== '') $out[] = $line; }
        return $out;
    }
    if ($kind === 'pairs') {
        $out = [];
        foreach (explode("\n", $raw) as $line) {
            if (trim($line) === '') continue;
            $parts = array_map('trim', explode('|', $line, 2));
            if ($parts[0] === '') continue;
            $out[] = [$parts[0], (float)str_replace(',', '.', $parts[1] ?? '0')];
        }
        return $out;
    }
    return $raw;
}

// Названия категорий для админки: базовые + добавленные вручную
function type_names(): array
{
    $out = [];
    foreach (categories() as $k => $c) $out[$k] = (string)$c['name'];
    return $out ?: ['bento' => 'Бенто'];
}

// ---------- helpers ----------
function admin_logged(): bool { return !empty($_SESSION['admin']); }

// ---------- пользователи панели ----------
function load_users(): array
{
    $d = json_decode((string)@file_get_contents(USERS_FILE), true);
    $users = [];
    foreach ($d['users'] ?? [] as $u) {
        if (!empty($u['email'])) $users[mb_strtolower($u['email'])] = $u + ['active' => true, 'role' => 'manager', 'last' => 0];
    }
    // переход со старой версии: единственный пароль превращается в администратора
    if (!$users && is_file(ADMIN_PASS_FILE)) {
        $email = mb_strtolower(EMAIL);
        $users[$email] = [
            'email' => $email, 'name' => 'Administrator', 'role' => 'admin', 'active' => true,
            'pass' => trim((string)file_get_contents(ADMIN_PASS_FILE)), 'created' => time(), 'last' => 0,
        ];
        save_users($users);
    }
    return $users;
}

function save_users(array $users): bool
{
    $ok = @file_put_contents(USERS_FILE, json_encode(
        ['users' => array_values($users)],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ));
    return $ok !== false;
}

function me(): ?array
{
    return load_users()[mb_strtolower((string)($_SESSION['user'] ?? ''))] ?? null;
}

function is_admin(): bool { return (me()['role'] ?? '') === 'admin'; }

// Доступен ли раздел текущему пользователю
function can(string $view): bool
{
    return is_admin() || in_array($view, MANAGER_VIEWS, true);
}

// Сколько активных администраторов останется — последнего не трогаем
function admins_left(array $users, string $exceptEmail = ''): int
{
    return count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'admin' && !empty($u['active']) && mb_strtolower($u['email']) !== $exceptEmail));
}
function csrf_ok(): bool { return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']); }
function flash(string $msg, string $kind = 'ok'): void { $_SESSION['flash'] = ['message' => $msg, 'kind' => $kind]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function go(string $path): never { header('Location: ' . $path); exit; }

function login_rl_path(): string { return __DIR__ . '/../uploads/ratelimit/adm-' . sha1($_SERVER['REMOTE_ADDR'] ?? '0') . '.json'; }
function login_blocked(): bool {
    $rl = json_decode((string)@file_get_contents(login_rl_path()), true) ?: ['t' => 0, 'n' => 0];
    return (time() - $rl['t'] < 900) && $rl['n'] >= 5;
}
function login_hit(): void {
    @mkdir(dirname(login_rl_path()), 0775, true);
    $rl = json_decode((string)@file_get_contents(login_rl_path()), true) ?: ['t' => time(), 'n' => 0];
    if (time() - $rl['t'] > 900) $rl = ['t' => time(), 'n' => 0];
    $rl['n']++;
    @file_put_contents(login_rl_path(), json_encode($rl));
}

function save_catalog(array $catalog): void {
    @mkdir(BACKUP_DIR, 0775, true);
    @copy(CATALOG_FILE, BACKUP_DIR . '/catalog-' . date('Ymd-His') . '.json');
    $old = glob(BACKUP_DIR . '/catalog-*.json') ?: [];
    if (count($old) > 20) { sort($old); foreach (array_slice($old, 0, count($old) - 20) as $f) @unlink($f); }
    $catalog['updated'] = date('Y-m-d');
    file_put_contents(CATALOG_FILE, json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

function make_slug(string $title, array $products): string {
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya','ə'=>'e','ğ'=>'g','ı'=>'i','ö'=>'o','ü'=>'u','ç'=>'c','ş'=>'s'];
    $s = strtr(mb_strtolower(trim($title)), $map);
    $s = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', $s)), '-') ?: 'tort';
    $slugs = array_column($products, 'slug');
    $base = $s; $i = 2;
    while (in_array($s, $slugs, true)) $s = $base . '-' . $i++;
    return $s;
}

function save_photo(array $f, string $slug): ?string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) return null;
    if ($f['size'] > 10 * 1024 * 1024) return null;
    if (!in_array((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']), ['image/jpeg', 'image/png', 'image/webp'], true)) return null;
    $src = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
    if (!$src) return null;
    $w = imagesx($src); $h = imagesy($src);
    $side = min($w, $h);
    $dst = imagecreatetruecolor(800, 800);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, (int)(($w - $side) / 2), (int)(($h - $side) / 2), 800, 800, $side, $side);
    @mkdir(PRODUCT_IMG_DIR, 0775, true);
    $name = $slug . '-' . bin2hex(random_bytes(4)) . '.jpg';
    $ok = imagejpeg($dst, PRODUCT_IMG_DIR . '/' . $name, 85);
    // превью 400×400 для сеток каталога
    $th = imagecreatetruecolor(400, 400);
    imagecopyresampled($th, $dst, 0, 0, 0, 0, 400, 400, 800, 800);
    @mkdir(THUMB_DIR, 0775, true);
    imagejpeg($th, THUMB_DIR . '/' . $name, 80);
    imagedestroy($src); imagedestroy($dst); imagedestroy($th);
    return $ok ? '/assets/img/products/' . $name : null;
}

function load_orders(): array
{
    $d = json_decode((string)@file_get_contents(ORDERS_FILE), true);
    return is_array($d['orders'] ?? null) ? $d['orders'] : [];
}

function save_orders(array $orders): void
{
    @mkdir(dirname(ORDERS_FILE), 0775, true);
    file_put_contents(ORDERS_FILE, json_encode(
        ['orders' => array_values($orders), 'updated' => date('Y-m-d H:i')],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ));
}

function save_categories(array $cats): void
{
    file_put_contents(CATEGORIES_FILE, json_encode(
        ['categories' => array_values($cats)],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ));
}

// Клиенты собираются из заказов: один телефон — один клиент
function build_customers(array $orders): array
{
    $out = [];
    foreach ($orders as $o) {
        $key = preg_replace('/\D+/', '', (string)($o['phone'] ?? ''));
        if ($key === '') continue;
        if (!isset($out[$key])) {
            $out[$key] = [
                'phone' => $o['phone'], 'name' => $o['name'], 'orders' => 0, 'sum' => 0.0,
                'first' => $o['created'], 'last' => $o['created'], 'items' => [], 'address' => '',
            ];
        }
        $c = &$out[$key];
        $c['orders']++;
        $c['sum'] += (float)preg_replace('/[^\d.]/', '', (string)($o['price'] ?? ''));
        $c['first'] = min($c['first'], $o['created']);
        if ($o['created'] >= $c['last']) { $c['last'] = $o['created']; $c['name'] = $o['name'] ?: $c['name']; }
        if ($c['address'] === '' && trim((string)($o['address'] ?? '')) !== '') $c['address'] = $o['address'];
        $c['items'][] = $o;
        unset($c);
    }
    uasort($out, fn($a, $b) => $b['last'] <=> $a['last']);
    return $out;
}

// Иконки для кнопок в таблицах: на узком экране остаётся только иконка
function icon(string $name): string
{
    $paths = [
        'open'   => '<path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'edit'   => '<path d="M4 20h4l10-10a2.1 2.1 0 0 0-3-3L5 17z"/><path d="M13.5 7.5l3 3"/>',
        'trash'  => '<path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13h10l1-13"/>',
        'wa'     => '<path d="M12 3a9 9 0 0 0-7.6 13.8L3 21l4.4-1.3A9 9 0 1 0 12 3z"/><path d="M8.5 9.2c.3 2.2 2.1 4 4.3 4.3"/>',
        'eye'    => '<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="2.6"/>',
    ];
    return '<svg class="bi" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
}

// ---------- routing ----------
$path = strtok($_SERVER['REQUEST_URI'] ?? '/admin/', '?');
$seg  = trim(str_replace('/admin', '', $path), '/');   // '' | products | designs | settings | account
$view = $seg === '' ? 'dashboard' : $seg;
$hasPass = (bool)load_users();
// сессия осталась от старой версии или пользователя отключили — выходим
if (admin_logged()) {
    $meNow = me();
    if (!$meNow || empty($meNow['active'])) {
        unset($_SESSION['admin'], $_SESSION['user']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { flash('Войдите заново.', 'bad'); go('/admin/'); }
    }
}
$err = '';

// ---------- POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setpass' && !$hasPass) {
        $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
        $name  = trim((string)($_POST['name'] ?? '')) ?: 'Administrator';
        $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Укажите настоящий e-mail — по нему будет вход.';
        elseif (mb_strlen($p1) < 8)  $err = 'Пароль должен быть не короче 8 символов.';
        elseif ($p1 !== $p2)         $err = 'Пароли не совпадают.';
        elseif (!save_users([$email => [
            'email' => $email, 'name' => $name, 'role' => 'admin', 'active' => true,
            'pass' => password_hash($p1, PASSWORD_DEFAULT), 'created' => time(), 'last' => 0,
        ]])) {
            $err = 'Не удалось записать data/users.json — нужны права на запись в папку data (chmod 755).';
        } else {
            flash('Пользователь создан — войдите.');
            go('/admin/');
        }
    } elseif ($action === 'login') {
        $users = load_users();
        $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
        $u     = $users[$email] ?? null;
        if (login_blocked())                          $err = 'Слишком много попыток. Попробуйте через 15 минут.';
        elseif ($u && empty($u['active']))            { login_hit(); $err = 'Доступ отключён — обратитесь к администратору.'; }
        elseif ($u && password_verify((string)($_POST['pass'] ?? ''), (string)$u['pass'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['user']  = $email;
            $_SESSION['csrf']  = bin2hex(random_bytes(16));
            $users[$email]['last'] = time();
            save_users($users);
            go('/admin/');
        } else { login_hit(); $err = 'Неверный e-mail или пароль.'; }
    } elseif ($action === 'logout') {
        unset($_SESSION['admin'], $_SESSION['user']);
        go('/admin/');
    } elseif (admin_logged() && csrf_ok()) {
        $catalog  = json_decode((string)@file_get_contents(CATALOG_FILE), true) ?: ['products' => []];
        $products = $catalog['products'];

        if ($action === 'save') {
            $slug  = trim((string)($_POST['slug'] ?? ''));
            $title = trim((string)($_POST['title'] ?? ''));
            $type  = (string)($_POST['type'] ?? 'bento');
            $price = trim((string)($_POST['price'] ?? ''));
            $seoT  = trim((string)($_POST['seo_title'] ?? ''));
            $seoD  = trim((string)($_POST['seo_desc'] ?? ''));
            if ($title === '' || $price === '' || !isset(type_names()[$type])) {
                flash('Заполните название, цену и тип.', 'bad');
                go('/admin/products' . ($slug ? '?edit=' . urlencode($slug) : '?add=1'));
            }
            $isNew = $slug === '';
            if ($isNew) $slug = make_slug($title, $products);
            $photo = isset($_FILES['photo']) ? save_photo($_FILES['photo'], $slug) : null;
            if ($isNew && !$photo) {
                flash('Для нового торта нужно фото: JPG, PNG или WebP.', 'bad');
                go('/admin/products?add=1');
            }
            $found = false;
            foreach ($products as &$p) {
                if ($p['slug'] === $slug) {
                    $p['title'] = $title; $p['type'] = $type; $p['price'] = $price;
                    $p['seo_title'] = $seoT !== '' ? $seoT : $title . ' - Vanilla.az';
                    $p['seo_desc']  = $seoD;
                    if ($photo) $p['img'] = $photo;
                    $found = true; break;
                }
            }
            unset($p);
            if (!$found) {
                array_unshift($products, [
                    'slug' => $slug, 'title' => $title, 'img' => $photo, 'price' => $price, 'type' => $type,
                    'seo_title' => $seoT !== '' ? $seoT : $title . ' - Vanilla.az', 'seo_desc' => $seoD,
                ]);
            }
            $catalog['products'] = $products;
            save_catalog($catalog);
            flash($isNew ? 'Торт добавлен в каталог.' : 'Изменения сохранены.');
            go('/admin/products');
        }

        if ($action === 'delete') {
            $slug = (string)($_POST['slug'] ?? '');
            $catalog['products'] = array_values(array_filter($products, fn($p) => $p['slug'] !== $slug));
            save_catalog($catalog);
            flash('Торт удалён из каталога.');
            go('/admin/products');
        }

        if ($action === 'profile') {
            $users = load_users();
            $email = mb_strtolower((string)($_SESSION['user'] ?? ''));
            if (isset($users[$email])) {
                $users[$email]['name'] = trim((string)($_POST['name'] ?? '')) ?: $users[$email]['name'];
                save_users($users);
                flash('Профиль сохранён.');
            }
            go('/admin/account');
        }

        if ($action === 'settings') {
            $data = [];
            foreach (SETTING_KEYS as $k) $data[$k] = trim((string)($_POST[$k] ?? ''));
            file_put_contents(SETTINGS_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            flash('Настройки сохранены.');
            go('/admin/settings');
        }

        if ($action === 'seo') {
            $in  = (array)($_POST['seo'] ?? []);
            $out = [];
            foreach (seo_keys() as $k) {
                $out[$k] = [
                    'title' => trim((string)($in[$k]['title'] ?? '')),
                    'desc'  => trim((string)($in[$k]['desc'] ?? '')),
                ];
            }
            save_seo($out);
            flash('SEO-данные сохранены.');
            go('/admin/seo');
        }

        if ($action === 'delete_design') {
            $f = basename((string)($_POST['file'] ?? ''));
            if ($f !== '' && preg_match('/^design-[\w-]+\.jpg$/', $f)) @unlink(DESIGN_DIR . '/' . $f);
            flash('Файл удалён.');
            go('/admin/designs');
        }

        if ($action === 'order_status') {
            $id  = (string)($_POST['id'] ?? '');
            $st  = (string)($_POST['status'] ?? '');
            $ord = load_orders();
            if (isset(ORDER_STATUSES[$st])) {
                foreach ($ord as &$o) if ($o['id'] === $id) { $o['status'] = $st; break; }
                unset($o);
                save_orders($ord);
                flash('Статус заказа обновлён.');
            }
            go('/admin/orders' . (($_POST['back'] ?? '') === 'card' ? '?id=' . urlencode($id) : ''));
        }

        if ($action === 'order_note') {
            $id  = (string)($_POST['id'] ?? '');
            $nt  = mb_substr(trim((string)($_POST['note'] ?? '')), 0, 500);
            $ord = load_orders();
            foreach ($ord as &$o) if ($o['id'] === $id) { $o['note'] = $nt; break; }
            unset($o);
            save_orders($ord);
            flash('Комментарий сохранён.');
            go('/admin/orders?id=' . urlencode($id));
        }

        if ($action === 'order_delete') {
            $id = (string)($_POST['id'] ?? '');
            save_orders(array_filter(load_orders(), fn($o) => $o['id'] !== $id));
            flash('Заказ удалён.');
            go('/admin/orders');
        }

        if ($action === 'page_save') {
            $pk = (string)($_POST['page'] ?? '');
            $l  = (string)($_POST['l'] ?? 'ru');
            if (!isset($PAGEMAP[$pk]) || !in_array($l, ['ru', 'az', 'en'], true)) go('/admin/pages');
            $page = $PAGEMAP[$pk];

            // SEO — одно на все языки, как было на vanilla.az
            if (!empty($page['seo'])) {
                $seo = load_seo();
                $seo[$page['seo']] = [
                    'title' => mb_substr(trim((string)($_POST['seo_title'] ?? '')), 0, 120),
                    'desc'  => mb_substr(trim((string)($_POST['seo_desc'] ?? '')), 0, 320),
                ];
                save_seo($seo);
            }

            // Тексты: храним только то, что отличается от перевода по умолчанию
            $def   = require __DIR__ . "/../lang/$l.php";
            $texts = load_texts();
            $cur   = $texts[$l] ?? [];
            $in    = (array)($_POST['tx'] ?? []);
            foreach ($page['groups'] as $keys) {
                foreach ($keys as $k) {
                    if (!array_key_exists($k, $in)) continue;
                    $val = field_value((string)$in[$k], field_kind($def[$k] ?? ''));
                    if ($val === '' || $val === [] || $val == ($def[$k] ?? null)) unset($cur[$k]);
                    else $cur[$k] = $val;
                }
            }
            if ($cur) $texts[$l] = $cur; else unset($texts[$l]);
            file_put_contents(TEXTS_FILE_ADM, json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            flash('Страница сохранена.');
            go('/admin/pages?edit=' . urlencode($pk) . '&l=' . $l);
        }

        if ($action === 'page_reset') {
            $pk = (string)($_POST['page'] ?? '');
            $l  = (string)($_POST['l'] ?? 'ru');
            if (isset($PAGEMAP[$pk]) && in_array($l, ['ru', 'az', 'en'], true)) {
                $texts = load_texts();
                foreach ($PAGEMAP[$pk]['groups'] as $keys) foreach ($keys as $k) unset($texts[$l][$k]);
                if (empty($texts[$l])) unset($texts[$l]);
                file_put_contents(TEXTS_FILE_ADM, json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                flash('Тексты страницы возвращены к исходным.');
            }
            go('/admin/pages?edit=' . urlencode($pk) . '&l=' . $l);
        }

        if ($action === 'cat_save') {
            $cats = categories();
            $key  = trim((string)($_POST['key'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $page = (string)($_POST['page'] ?? 'bento');
            if ($name === '' || !isset(CAT_PAGES[$page])) {
                flash('Укажите название категории.', 'bad');
                go('/admin/categories');
            }
            if ($key === '') {                                   // новая категория
                $key = make_slug($name, array_map(fn($c) => ['slug' => $c['key']], array_values($cats)));
                $cats[$key] = ['key' => $key, 'builtin' => false];
            } elseif (!isset($cats[$key])) {
                go('/admin/categories');
            }
            $cats[$key]['name']    = $name;
            $cats[$key]['name_az'] = trim((string)($_POST['name_az'] ?? ''));
            $cats[$key]['name_en'] = trim((string)($_POST['name_en'] ?? ''));
            $cats[$key]['desc']    = mb_substr(trim((string)($_POST['desc'] ?? '')), 0, 300);
            if (empty($cats[$key]['builtin'])) {
                $cats[$key]['page'] = $page;
                if ($page === 'own' && empty($cats[$key]['slug'])) {
                    $cats[$key]['slug'] = make_slug($name, array_map(fn($c) => ['slug' => $c['slug'] ?? $c['key']], array_values($cats)));
                }
            }
            save_categories($cats);
            flash('Категория сохранена.');
            go('/admin/categories');
        }

        if ($action === 'cat_delete') {
            $cats = categories();
            $key  = (string)($_POST['key'] ?? '');
            $used = count(array_filter($products, fn($p) => $p['type'] === $key));
            if (empty($cats[$key]) || !empty($cats[$key]['builtin'])) flash('Базовую категорию удалить нельзя.', 'bad');
            elseif ($used) flash('Сначала перенесите товары (' . $used . ' шт.) в другую категорию.', 'bad');
            else { unset($cats[$key]); save_categories($cats); flash('Категория удалена.'); }
            go('/admin/categories');
        }

        if ($action === 'user_add' && is_admin()) {
            $users = load_users();
            $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
            $name  = trim((string)($_POST['name'] ?? '')) ?: $email;
            $role  = isset(ROLES[(string)($_POST['role'] ?? '')]) ? (string)$_POST['role'] : 'manager';
            $pass  = (string)($_POST['pass'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) flash('Укажите настоящий e-mail — по нему будет вход.', 'bad');
            elseif (isset($users[$email]))                  flash('Пользователь с таким e-mail уже есть.', 'bad');
            elseif (mb_strlen($pass) < 8)                   flash('Пароль должен быть не короче 8 символов.', 'bad');
            else {
                $users[$email] = [
                    'email' => $email, 'name' => $name, 'role' => $role, 'active' => true,
                    'pass' => password_hash($pass, PASSWORD_DEFAULT), 'created' => time(), 'last' => 0,
                ];
                save_users($users);
                flash('Пользователь добавлен.');
            }
            go('/admin/users');
        }

        if ($action === 'users_save' && is_admin()) {
            $users = load_users();
            $in    = (array)($_POST['u'] ?? []);
            $meMail = mb_strtolower((string)($_SESSION['user'] ?? ''));
            $saved = 0; $warn = '';
            foreach ($users as $email => $row) {
                $d = $in[$email] ?? null;
                if (!is_array($d)) continue;
                $name   = trim((string)($d['name'] ?? '')) ?: $row['name'];
                $role   = isset(ROLES[(string)($d['role'] ?? '')]) ? (string)$d['role'] : $row['role'];
                $active = !empty($d['active']);
                $pass   = (string)($d['pass'] ?? '');
                // последнего активного администратора не понижаем и не отключаем
                $wasAdmin = ($row['role'] ?? '') === 'admin' && !empty($row['active']);
                if ($wasAdmin && ($role !== 'admin' || !$active) && admins_left($users, $email) === 0) {
                    $role = 'admin'; $active = true;
                    $warn = 'Это единственный администратор — роль и доступ оставлены как были.';
                }
                if ($email === $meMail) $active = true;          // себя не отключаем
                if ($pass !== '' && mb_strlen($pass) < 8) {
                    $warn = 'Пароль короче 8 символов не сохранён.';
                    $pass = '';
                }
                $users[$email]['name']   = $name;
                $users[$email]['role']   = $role;
                $users[$email]['active'] = $active;
                if ($pass !== '') $users[$email]['pass'] = password_hash($pass, PASSWORD_DEFAULT);
                $saved++;
            }
            save_users($users);
            flash($warn ?: 'Изменения сохранены.', $warn ? 'bad' : 'ok');
            go('/admin/users');
        }

        if ($action === 'user_delete' && is_admin()) {
            $users = load_users();
            $email = mb_strtolower((string)($_POST['email'] ?? ''));
            if ($email === mb_strtolower((string)($_SESSION['user'] ?? '')))               flash('Себя удалить нельзя.', 'bad');
            elseif (!isset($users[$email]))                                                flash('Пользователь не найден.', 'bad');
            elseif (($users[$email]['role'] ?? '') === 'admin' && admins_left($users, $email) === 0) flash('Это единственный администратор.', 'bad');
            else { unset($users[$email]); save_users($users); flash('Пользователь удалён.'); }
            go('/admin/users');
        }

        if ($action === 'chpass') {
            $cur = (string)($_POST['cur'] ?? ''); $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
            $users = load_users();
            $email = mb_strtolower((string)($_SESSION['user'] ?? ''));
            if (!isset($users[$email]) || !password_verify($cur, (string)$users[$email]['pass'])) flash('Текущий пароль неверный.', 'bad');
            elseif (mb_strlen($p1) < 8)  flash('Новый пароль должен быть не короче 8 символов.', 'bad');
            elseif ($p1 !== $p2)         flash('Новые пароли не совпадают.', 'bad');
            else {
                $users[$email]['pass'] = password_hash($p1, PASSWORD_DEFAULT);
                save_users($users);
                flash('Пароль изменён.');
            }
            go('/admin/account');
        }
    }
}

// ---------- data for views ----------
$catalog  = json_decode((string)@file_get_contents(CATALOG_FILE), true) ?: ['products' => []];
$products = $catalog['products'];
$note     = take_flash();
$designs  = [];
foreach (glob(DESIGN_DIR . '/*.jpg') ?: [] as $f) {
    $designs[] = ['name' => basename($f), 'time' => filemtime($f), 'size' => filesize($f)];
}
usort($designs, fn($a, $b) => $b['time'] <=> $a['time']);
$orders    = load_orders();
$cats      = categories();
$customers = build_customers($orders);
$newOrders = count(array_filter($orders, fn($o) => ($o['status'] ?? 'new') === 'new'));

$users = load_users();
$titles = ['dashboard' => 'Обзор', 'orders' => 'Заказы', 'customers' => 'Клиенты', 'pages' => 'Страницы', 'products' => 'Товары', 'categories' => 'Категории', 'designs' => 'Дизайны клиентов', 'seo' => 'SEO страниц', 'settings' => 'Контакты и карта', 'users' => 'Пользователи', 'account' => 'Мой профиль'];
$title  = $titles[$view] ?? 'Админ';
if (!array_key_exists($view, $titles)) { $view = 'dashboard'; $title = $titles['dashboard']; }
if (admin_logged() && !can($view)) {
    flash('Этот раздел доступен только администратору.', 'bad');
    go('/admin/');
}

require __DIR__ . '/views/layout.php';
