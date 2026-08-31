<?php
// ===== Админ-панель Vanilla: управление каталогом (data/catalog.json) =====
require __DIR__ . '/includes/config.php';

const ADMIN_PASS_FILE = __DIR__ . '/data/admin.pass';
const BACKUP_DIR      = __DIR__ . '/data/backups';
const PRODUCT_IMG_DIR = __DIR__ . '/assets/img/products';

function admin_csrf_ok(): bool
{
    return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function admin_logged(): bool
{
    return !empty($_SESSION['admin']);
}

// Rate-limit входа: 5 попыток / 15 минут / IP
function login_rl_path(): string
{
    return __DIR__ . '/uploads/ratelimit/adm-' . sha1($_SERVER['REMOTE_ADDR'] ?? '0') . '.json';
}
function login_rl_blocked(): bool
{
    $rl = json_decode((string)@file_get_contents(login_rl_path()), true) ?: ['t' => 0, 'n' => 0];
    return (time() - $rl['t'] < 900) && $rl['n'] >= 5;
}
function login_rl_hit(): void
{
    @mkdir(dirname(login_rl_path()), 0775, true);
    $rl = json_decode((string)@file_get_contents(login_rl_path()), true) ?: ['t' => time(), 'n' => 0];
    if (time() - $rl['t'] > 900) $rl = ['t' => time(), 'n' => 0];
    $rl['n']++;
    @file_put_contents(login_rl_path(), json_encode($rl));
}

function save_catalog(array $catalog): void
{
    @mkdir(BACKUP_DIR, 0775, true);
    @copy(CATALOG_FILE, BACKUP_DIR . '/catalog-' . date('Ymd-His') . '.json');
    $backups = glob(BACKUP_DIR . '/catalog-*.json') ?: [];
    if (count($backups) > 20) {
        sort($backups);
        foreach (array_slice($backups, 0, count($backups) - 20) as $old) @unlink($old);
    }
    $catalog['updated'] = date('Y-m-d');
    file_put_contents(CATALOG_FILE, json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

function make_slug(string $title, array $products): string
{
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya','ə'=>'e','ğ'=>'g','ı'=>'i','ö'=>'o','ü'=>'u','ç'=>'c','ş'=>'s'];
    $s = mb_strtolower(trim($title));
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim(preg_replace('/-+/', '-', $s), '-') ?: 'tort';
    $slugs = array_column($products, 'slug');
    $base = $s; $i = 2;
    while (in_array($s, $slugs, true)) $s = $base . '-' . $i++;
    return $s;
}

// Загрузка фото товара: контентная проверка + пересжатие GD (как upload-design)
function save_product_photo(array $f, string $slug): ?string
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) return null;
    if ($f['size'] > 10 * 1024 * 1024) return null;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) return null;
    $src = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
    if (!$src) return null;
    $w = imagesx($src); $h = imagesy($src);
    $side = min($w, $h);
    $x = (int)(($w - $side) / 2); $y = (int)(($h - $side) / 2);
    $dst = imagecreatetruecolor(800, 800);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);
    imagecopyresampled($dst, $src, 0, 0, $x, $y, 800, 800, $side, $side);
    @mkdir(PRODUCT_IMG_DIR, 0775, true);
    $name = $slug . '-' . bin2hex(random_bytes(4)) . '.jpg';
    if (!imagejpeg($dst, PRODUCT_IMG_DIR . '/' . $name, 85)) return null;
    imagedestroy($src); imagedestroy($dst);
    return '/assets/img/products/' . $name;
}

$err = '';
$ok  = '';
$hasPass = is_file(ADMIN_PASS_FILE);

// ===== POST actions =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setpass' && !$hasPass) {
        $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
        if (mb_strlen($p1) < 8) $err = 'Пароль минимум 8 символов.';
        elseif ($p1 !== $p2) $err = 'Пароли не совпадают.';
        else {
            file_put_contents(ADMIN_PASS_FILE, password_hash($p1, PASSWORD_DEFAULT));
            $hasPass = true;
            $ok = 'Пароль установлен — войдите.';
        }
    } elseif ($action === 'login') {
        if (login_rl_blocked()) $err = 'Слишком много попыток — подождите 15 минут.';
        elseif ($hasPass && password_verify((string)($_POST['pass'] ?? ''), (string)file_get_contents(ADMIN_PASS_FILE))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
            header('Location: /admin/'); exit;
        } else { login_rl_hit(); $err = 'Неверный пароль.'; }
    } elseif ($action === 'logout' && admin_logged()) {
        unset($_SESSION['admin']);
        header('Location: /admin/'); exit;
    } elseif (admin_logged() && admin_csrf_ok()) {
        $products = $CATALOG['products'];
        if ($action === 'delete') {
            $slug = (string)($_POST['slug'] ?? '');
            $products = array_values(array_filter($products, fn($p) => $p['slug'] !== $slug));
            $CATALOG['products'] = $products;
            save_catalog($CATALOG);
            header('Location: /admin/?ok=del'); exit;
        }
        if ($action === 'save') {
            $slug  = trim((string)($_POST['slug'] ?? ''));
            $title = trim((string)($_POST['title'] ?? ''));
            $type  = (string)($_POST['type'] ?? 'bento');
            $price = trim((string)($_POST['price'] ?? ''));
            $seoT  = trim((string)($_POST['seo_title'] ?? ''));
            $seoD  = trim((string)($_POST['seo_desc'] ?? ''));
            if ($title === '' || $price === '' || !in_array($type, ['bento', 'bantik', 'set', 'ctg'], true)) {
                $err = 'Заполните название, цену и тип.';
            } else {
                $isNew = $slug === '';
                if ($isNew) $slug = make_slug($title, $products);
                $photo = isset($_FILES['photo']) ? save_product_photo($_FILES['photo'], $slug) : null;
                $found = false;
                foreach ($products as &$p) {
                    if ($p['slug'] === $slug) {
                        $p['title'] = $title; $p['type'] = $type; $p['price'] = $price;
                        $p['seo_title'] = $seoT !== '' ? $seoT : $title . ' - Vanilla.az';
                        $p['seo_desc'] = $seoD;
                        if ($photo) $p['img'] = $photo;
                        $found = true; break;
                    }
                }
                unset($p);
                if (!$found) {
                    if (!$photo) { $err = 'Для нового товара нужно фото (JPG/PNG/WebP).'; }
                    else {
                        array_unshift($products, [
                            'slug' => $slug, 'title' => $title, 'img' => $photo, 'price' => $price,
                            'type' => $type, 'seo_title' => $seoT !== '' ? $seoT : $title . ' - Vanilla.az', 'seo_desc' => $seoD,
                        ]);
                        $found = true;
                    }
                }
                if ($found && $err === '') {
                    $CATALOG['products'] = $products;
                    save_catalog($CATALOG);
                    header('Location: /admin/?ok=save'); exit;
                }
            }
        }
    }
}

$okMsg = ['save' => 'Сохранено.', 'del' => 'Товар удалён.'][$_GET['ok'] ?? ''] ?? $ok;
$products = json_decode((string)@file_get_contents(CATALOG_FILE), true)['products'] ?? [];
$editSlug = (string)($_GET['edit'] ?? '');
$edit = null;
foreach ($products as $p) if ($p['slug'] === $editSlug) { $edit = $p; break; }
$showForm = $edit || isset($_GET['add']);
$typeNames = ['bento' => 'Бенто', 'bantik' => 'Бантик', 'set' => 'Сет', 'ctg' => 'Cake to go'];
$filter = (string)($_GET['type'] ?? '');
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Vanilla — админ-панель</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔐</text></svg>">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#F4F6F9;color:#2A4159;font-size:14px;line-height:1.6}
.wrap{max-width:1080px;margin:0 auto;padding:28px 20px 60px}
h1{font-size:22px;color:#16527F;margin-bottom:4px}
.sub{color:#7b8a99;font-size:13px;margin-bottom:24px}
.card{background:#fff;border:1px solid #E4E8EE;border-radius:14px;padding:22px;margin-bottom:20px}
.msg{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600}
.msg.ok{background:#EAF8EF;color:#1F7A45}
.msg.err{background:#FDEBEE;color:#C4344C}
label{display:block;font-size:12px;font-weight:700;color:#16527F;margin:12px 0 4px}
input[type=text],input[type=password],select,textarea{width:100%;padding:10px 12px;border:1px solid #D7DEE6;border-radius:9px;font:inherit;background:#fff}
textarea{min-height:64px;resize:vertical}
input:focus,select:focus,textarea:focus{outline:none;border-color:#FD4680;box-shadow:0 0 0 3px rgba(253,70,128,.12)}
.btn{display:inline-block;padding:11px 22px;border:none;border-radius:999px;background:#FD4680;color:#fff;font-weight:700;font-size:13.5px;cursor:pointer;text-decoration:none;transition:.2s}
.btn:hover{background:#E02D68}
.btn.gray{background:#E8EDF2;color:#2A4159}
.btn.sm{padding:7px 14px;font-size:12.5px}
.btn.danger{background:#fff;color:#C4344C;border:1px solid #F3C1CB}
.topline{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #E4E8EE}
th{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#7b8a99;text-align:left;padding:12px 14px;border-bottom:1px solid #E4E8EE;background:#FAFBFD}
td{padding:10px 14px;border-bottom:1px solid #EEF1F5;vertical-align:middle}
tr:last-child td{border-bottom:none}
td img{width:52px;height:52px;border-radius:10px;object-fit:cover;display:block}
.tag{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:700}
.tag.bento{background:#EEF6FC;color:#16527F}.tag.bantik{background:#FFF3F8;color:#E02D68}
.tag.set{background:#F3EEFC;color:#6E4AA8}.tag.ctg{background:#EFF8F1;color:#1F7A45}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}
.filter{display:flex;gap:8px;flex-wrap:wrap}
.filter a{padding:8px 15px;border-radius:99px;background:#fff;border:1px solid #D7DEE6;font-size:12.5px;font-weight:600;color:#2A4159;text-decoration:none}
.filter a.on{background:#16527F;border-color:#16527F;color:#fff}
.login{max-width:380px;margin:80px auto}
@media(max-width:720px){.grid2{grid-template-columns:1fr}.hide-m{display:none}}
</style>
</head>
<body>
<div class="wrap">

<?php if (!admin_logged()): ?>
  <div class="login">
    <div class="card">
      <h1>🍰 Vanilla — админ</h1>
      <p class="sub"><?= $hasPass ? 'Введите пароль' : 'Первый вход: придумайте пароль (минимум 8 символов)' ?></p>
      <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
      <?php if ($okMsg): ?><div class="msg ok"><?= e($okMsg) ?></div><?php endif; ?>
      <?php if ($hasPass): ?>
      <form method="post">
        <input type="hidden" name="action" value="login">
        <label>Пароль</label>
        <input type="password" name="pass" autofocus required>
        <p style="margin-top:16px"><button class="btn">Войти</button></p>
      </form>
      <?php else: ?>
      <form method="post">
        <input type="hidden" name="action" value="setpass">
        <label>Новый пароль</label>
        <input type="password" name="p1" minlength="8" autofocus required>
        <label>Повторите пароль</label>
        <input type="password" name="p2" minlength="8" required>
        <p style="margin-top:16px"><button class="btn">Создать пароль</button></p>
      </form>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <div class="topline">
    <div>
      <h1>🍰 Каталог тортов</h1>
      <p class="sub"><?= count($products) ?> товаров · <a href="/" target="_blank">открыть сайт ↗</a></p>
    </div>
    <div style="display:flex;gap:8px">
      <a class="btn" href="/admin/?add=1">+ Добавить товар</a>
      <form method="post" style="display:inline"><input type="hidden" name="action" value="logout"><button class="btn gray">Выйти</button></form>
    </div>
  </div>

  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($okMsg): ?><div class="msg ok"><?= e($okMsg) ?></div><?php endif; ?>

  <?php if ($showForm): ?>
  <div class="card">
    <h1 style="font-size:17px;margin-bottom:6px"><?= $edit ? 'Редактировать: ' . e($edit['title']) : 'Новый товар' ?></h1>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="slug" value="<?= e($edit['slug'] ?? '') ?>">
      <div class="grid2">
        <div>
          <label>Название *</label>
          <input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required>
          <label>Тип *</label>
          <select name="type">
            <?php foreach ($typeNames as $tk => $tn): ?>
            <option value="<?= $tk ?>" <?= ($edit['type'] ?? '') === $tk ? 'selected' : '' ?>><?= $tn ?></option>
            <?php endforeach; ?>
          </select>
          <label>Цена * (например: 25 – 60 ₼)</label>
          <input type="text" name="price" value="<?= e($edit['price'] ?? '') ?>" required>
          <label>Фото <?= $edit ? '(оставьте пустым, чтобы не менять)' : '*' ?></label>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
          <?php if ($edit): ?><p style="margin-top:8px"><img src="<?= e($edit['img']) ?>" style="width:90px;height:90px;border-radius:12px;object-fit:cover"></p><?php endif; ?>
        </div>
        <div>
          <label>SEO title (пусто = автоматически)</label>
          <input type="text" name="seo_title" value="<?= e($edit['seo_title'] ?? '') ?>">
          <label>SEO description</label>
          <textarea name="seo_desc"><?= e($edit['seo_desc'] ?? '') ?></textarea>
          <?php if ($edit): ?>
          <label>Адрес страницы (не меняется — важно для SEO)</label>
          <input type="text" value="/mehsul/<?= e($edit['slug']) ?>/" readonly style="background:#F4F6F9;color:#7b8a99">
          <?php endif; ?>
        </div>
      </div>
      <p style="margin-top:18px;display:flex;gap:10px">
        <button class="btn">Сохранить</button>
        <a class="btn gray" href="/admin/">Отмена</a>
      </p>
    </form>
  </div>
  <?php endif; ?>

  <div class="topline">
    <div class="filter">
      <a href="/admin/" class="<?= $filter === '' ? 'on' : '' ?>">Все</a>
      <?php foreach ($typeNames as $tk => $tn): ?>
      <a href="/admin/?type=<?= $tk ?>" class="<?= $filter === $tk ? 'on' : '' ?>"><?= $tn ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <table>
    <tr><th></th><th>Название</th><th class="hide-m">Тип</th><th class="hide-m">Цена</th><th style="width:180px"></th></tr>
    <?php foreach ($products as $p): if ($filter && $p['type'] !== $filter) continue; ?>
    <tr>
      <td><img loading="lazy" src="<?= e($p['img']) ?>" alt=""></td>
      <td><b><?= e($p['title']) ?></b><br><small style="color:#7b8a99">/mehsul/<?= e($p['slug']) ?>/</small></td>
      <td class="hide-m"><span class="tag <?= e($p['type']) ?>"><?= $typeNames[$p['type']] ?? e($p['type']) ?></span></td>
      <td class="hide-m"><?= e($p['price']) ?></td>
      <td style="text-align:right;white-space:nowrap">
        <a class="btn sm gray" href="/admin/?edit=<?= e($p['slug']) ?>">Изменить</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Удалить «<?= e($p['title']) ?>»?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
          <input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
          <button class="btn sm danger">Удалить</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</div>
</body>
</html>
