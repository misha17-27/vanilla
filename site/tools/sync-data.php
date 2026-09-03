<?php
/**
 * Перенос данных из репозитория на сервер при деплое.
 *
 * Правила простые: то, что уже есть на сервере, остаётся нетронутым —
 * правки из админки не теряются. Приезжает только новое: товары, которых
 * там нет, новые категории, новые ключи SEO и текстов.
 *
 * Запускается из .cpanel.yml:  php tools/sync-data.php /home/vanilla/public_html
 * Заказы, пользователи, расписание и настройки не трогаются никогда.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$src = __DIR__ . '/../data';
$dst = rtrim($argv[1] ?? '', '/') . '/data';
if (!is_dir($dst)) {
    fwrite(STDERR, "Нет папки $dst\n");
    exit(1);
}

function readJson(string $f): ?array
{
    if (!is_file($f)) return null;
    $d = json_decode((string)file_get_contents($f), true);
    return is_array($d) ? $d : null;
}

function writeJson(string $f, array $d): void
{
    file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

function backup(string $f): void
{
    if (!is_file($f)) return;
    $dir = dirname($f) . '/backups';
    @mkdir($dir, 0775, true);
    @copy($f, $dir . '/' . basename($f, '.json') . '-' . date('Ymd-His') . '-deploy.json');
}

$log = [];

// ---------- каталог: добавляем товары, которых на сервере нет ----------
$a = readJson("$src/catalog.json");
$b = readJson("$dst/catalog.json");
if ($a && $b) {
    $have = array_flip(array_column($b['products'] ?? [], 'slug'));
    $add  = array_values(array_filter($a['products'] ?? [], fn($p) => !isset($have[$p['slug']])));
    if ($add) {
        backup("$dst/catalog.json");
        $b['products'] = array_merge($add, $b['products']);
        $b['updated']  = date('Y-m-d');
        writeJson("$dst/catalog.json", $b);
    }
    $log[] = 'каталог: добавлено ' . count($add) . ', на сервере было ' . count($have);
} elseif ($a && !$b) {
    copy("$src/catalog.json", "$dst/catalog.json");
    $log[] = 'каталог: скопирован целиком (на сервере его не было)';
}

// ---------- категории: добавляем новые ключи ----------
$a = readJson("$src/categories.json");
$b = readJson("$dst/categories.json");
if ($a && $b) {
    $have = array_flip(array_column($b['categories'] ?? [], 'key'));
    $add  = array_values(array_filter($a['categories'] ?? [], fn($c) => !isset($have[$c['key']])));
    if ($add) {
        backup("$dst/categories.json");
        $b['categories'] = array_merge($b['categories'], $add);
        writeJson("$dst/categories.json", $b);
    }
    $log[] = 'категории: добавлено ' . count($add);
} elseif ($a && !$b) {
    copy("$src/categories.json", "$dst/categories.json");
    $log[] = 'категории: скопированы целиком';
}

// ---------- SEO и тексты: только недостающие ключи ----------
foreach (['seo.json', 'texts.json'] as $file) {
    $a = readJson("$src/$file");
    $b = readJson("$dst/$file");
    if (!$a) continue;
    if (!$b) { copy("$src/$file", "$dst/$file"); $log[] = "$file: скопирован целиком"; continue; }
    $added = 0;
    foreach ($a as $k => $v) {
        if (!array_key_exists($k, $b)) { $b[$k] = $v; $added++; continue; }
        // вложенные наборы (языки в texts.json) дополняем по ключам
        if (is_array($v) && is_array($b[$k])) {
            foreach ($v as $k2 => $v2) {
                if (!array_key_exists($k2, $b[$k])) { $b[$k][$k2] = $v2; $added++; }
            }
        }
    }
    if ($added) { backup("$dst/$file"); writeJson("$dst/$file", $b); }
    $log[] = "$file: добавлено ключей $added";
}

// ---------- витринные данные, которые ведём только в репозитории ----------
foreach (['reviews.json', 'instagram.json'] as $file) {
    if (is_file("$src/$file")) {
        copy("$src/$file", "$dst/$file");
        $log[] = "$file: обновлён";
    }
}

foreach ($log as $l) echo "  $l\n";
