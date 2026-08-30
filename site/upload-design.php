<?php
// Загрузка фото «своего дизайна» с страницы товара.
// Защита: CSRF-токен, лимит по IP, проверка типа по содержимому (finfo + getimagesize)
// и полное пересжатие через GD — на диск попадает только чистый JPEG со случайным именем.
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

const MAX_BYTES   = 8 * 1024 * 1024; // 8 MB
const MAX_SIDE    = 1600;            // px после пересжатия
const RATE_LIMIT  = 10;              // загрузок в час с одного IP

function fail(string $code, int $http = 400): void
{
    http_response_code($http);
    echo json_encode(['ok' => false, 'error' => $code]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    fail('method', 405);
}

// CSRF: токен выдаётся в сессии на странице товара
$csrf = $_POST['csrf'] ?? '';
if (!is_string($csrf) || $csrf === '' || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    fail('csrf', 403);
}

// Лимит по IP
$ip     = $_SERVER['REMOTE_ADDR'] ?? '0';
$rlDir  = __DIR__ . '/uploads/ratelimit';
@mkdir($rlDir, 0775, true);
$rlFile = $rlDir . '/' . sha1($ip) . '.json';
$rl = json_decode((string)@file_get_contents($rlFile), true) ?: ['t' => time(), 'n' => 0];
if (time() - ($rl['t'] ?? 0) > 3600) {
    $rl = ['t' => time(), 'n' => 0];
}
if (($rl['n'] ?? 0) >= RATE_LIMIT) {
    fail('rate', 429);
}

$f = $_FILES['photo'] ?? null;
if (!$f || !is_uploaded_file($f['tmp_name'] ?? '')) {
    fail('generic');
}
if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    fail(in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'size' : 'generic');
}
if ((int)$f['size'] > MAX_BYTES) {
    fail('size');
}

// Тип определяем по СОДЕРЖИМОМУ, а не по имени файла
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    fail('type');
}
$info = @getimagesize($f['tmp_name']);
if ($info === false || $info[0] < 10 || $info[1] < 10 || $info[0] > 9000 || $info[1] > 9000) {
    fail('type');
}

// Пересжатие: срезает EXIF и любые встроенные данные — сохраняем только пиксели
$src = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
if ($src === false) {
    fail('type');
}
$w = imagesx($src);
$h = imagesy($src);
$scale = min(1, MAX_SIDE / max($w, $h));
$nw = max(1, (int)round($w * $scale));
$nh = max(1, (int)round($h * $scale));
$dst = imagecreatetruecolor($nw, $nh);
$white = imagecolorallocate($dst, 255, 255, 255);
imagefill($dst, 0, 0, $white); // прозрачность PNG/WebP -> белый фон
imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

$dir = __DIR__ . '/uploads/designs';
@mkdir($dir, 0775, true);
$name = 'design-' . date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.jpg';
if (!imagejpeg($dst, $dir . '/' . $name, 85)) {
    fail('generic', 500);
}
imagedestroy($src);
imagedestroy($dst);

$rl['n'] = ($rl['n'] ?? 0) + 1;
@file_put_contents($rlFile, json_encode($rl));

// Абсолютная ссылка по хосту запроса: на проде это https://vanilla.az/uploads/... —
// WhatsApp сделает её кликабельной (IP-адреса вроде 127.0.0.1 WhatsApp ссылками не считает).
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'vanilla.az';
echo json_encode(['ok' => true, 'url' => $scheme . '://' . $host . '/uploads/designs/' . $name]);
