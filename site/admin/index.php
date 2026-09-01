<?php
// ===== Админ-панель Vanilla Cake =====
require __DIR__ . '/../includes/config.php';

const ADMIN_PASS_FILE = __DIR__ . '/../data/admin.pass';
const BACKUP_DIR      = __DIR__ . '/../data/backups';
const PRODUCT_IMG_DIR = __DIR__ . '/../assets/img/products';
const DESIGN_DIR      = __DIR__ . '/../uploads/designs';
const TYPE_NAMES      = ['bento' => 'Бенто', 'bantik' => 'Бантик', 'set' => 'Сет', 'ctg' => 'Cake to go'];

// ---------- helpers ----------
function admin_logged(): bool { return !empty($_SESSION['admin']); }
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
    imagedestroy($src); imagedestroy($dst);
    return $ok ? '/assets/img/products/' . $name : null;
}

// ---------- routing ----------
$path = strtok($_SERVER['REQUEST_URI'] ?? '/admin/', '?');
$seg  = trim(str_replace('/admin', '', $path), '/');   // '' | products | designs | settings | account
$view = $seg === '' ? 'dashboard' : $seg;
$hasPass = is_file(ADMIN_PASS_FILE);
$err = '';

// ---------- POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setpass' && !$hasPass) {
        $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
        if (mb_strlen($p1) < 8)      $err = 'Пароль должен быть не короче 8 символов.';
        elseif ($p1 !== $p2)         $err = 'Пароли не совпадают.';
        else {
            file_put_contents(ADMIN_PASS_FILE, password_hash($p1, PASSWORD_DEFAULT));
            flash('Пароль создан — войдите.');
            go('/admin/');
        }
    } elseif ($action === 'login') {
        if (login_blocked())                                            $err = 'Слишком много попыток. Попробуйте через 15 минут.';
        elseif ($hasPass && password_verify((string)($_POST['pass'] ?? ''), (string)file_get_contents(ADMIN_PASS_FILE))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['csrf']  = bin2hex(random_bytes(16));
            go('/admin/');
        } else { login_hit(); $err = 'Неверный пароль.'; }
    } elseif ($action === 'logout') {
        unset($_SESSION['admin']);
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
            if ($title === '' || $price === '' || !isset(TYPE_NAMES[$type])) {
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
            foreach (['home', 'bento', 'ctg', 'about', 'faq', 'contact'] as $k) {
                $out[$k] = [
                    'title' => trim((string)($in[$k]['title'] ?? '')),
                    'desc'  => trim((string)($in[$k]['desc'] ?? '')),
                ];
            }
            file_put_contents(__DIR__ . '/../data/seo.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            flash('SEO-данные сохранены.');
            go('/admin/seo');
        }

        if ($action === 'delete_design') {
            $f = basename((string)($_POST['file'] ?? ''));
            if ($f !== '' && preg_match('/^design-[\w-]+\.jpg$/', $f)) @unlink(DESIGN_DIR . '/' . $f);
            flash('Файл удалён.');
            go('/admin/designs');
        }

        if ($action === 'chpass') {
            $cur = (string)($_POST['cur'] ?? ''); $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
            if (!password_verify($cur, (string)file_get_contents(ADMIN_PASS_FILE))) flash('Текущий пароль неверный.', 'bad');
            elseif (mb_strlen($p1) < 8)  flash('Новый пароль должен быть не короче 8 символов.', 'bad');
            elseif ($p1 !== $p2)         flash('Новые пароли не совпадают.', 'bad');
            else { file_put_contents(ADMIN_PASS_FILE, password_hash($p1, PASSWORD_DEFAULT)); flash('Пароль изменён.'); }
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

$titles = ['dashboard' => 'Обзор', 'products' => 'Каталог тортов', 'designs' => 'Дизайны клиентов', 'seo' => 'SEO страниц', 'settings' => 'Контакты и карта', 'account' => 'Пароль и безопасность'];
$title  = $titles[$view] ?? 'Админ';
if (!array_key_exists($view, $titles)) { $view = 'dashboard'; $title = $titles['dashboard']; }

require __DIR__ . '/views/layout.php';
