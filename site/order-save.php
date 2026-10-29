<?php
// Сохранение заказа перед отправкой в WhatsApp: пишем в data/orders.json,
// чтобы в админке была история заказов и клиентов.
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/orders.php';
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

const ORDER_RATE  = 20; // заказов в час с одного IP

function bad(string $code, int $http = 400): never {
    http_response_code($http);
    echo json_encode(['ok' => false, 'error' => $code]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') bad('method', 405);

$raw = file_get_contents('php://input');
if (strlen($raw) > 20000) bad('size');
$in = json_decode($raw, true);
if (!is_array($in)) bad('json');
if (!isset($in['csrf']) || !is_string($in['csrf']) || !hash_equals($_SESSION['csrf'], $in['csrf'])) bad('csrf', 403);
if (!turnstile_ok((string)($in['captcha'] ?? ''))) bad('captcha', 403);

// лимит по IP
$rlDir = __DIR__ . '/uploads/ratelimit';
@mkdir($rlDir, 0775, true);
$rlFile = $rlDir . '/ord-' . sha1($_SERVER['REMOTE_ADDR'] ?? '0') . '.json';
$rl = json_decode((string)@file_get_contents($rlFile), true) ?: ['t' => time(), 'n' => 0];
if (time() - $rl['t'] > 3600) $rl = ['t' => time(), 'n' => 0];
if ($rl['n'] >= ORDER_RATE) bad('rate', 429);

// нормализуем поля: только строки, без управляющих символов, с ограничением длины
function clean($v, int $max = 200): string {
    if (!is_string($v)) return '';
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $v);
    return mb_substr(trim($v), 0, $max);
}
function cleanList($v, int $max = 200, int $limit = 5): array {
    if (!is_array($v)) return [];
    $out = [];
    foreach (array_slice($v, 0, $limit) as $x) {
        $s = clean($x, $max);
        if ($s !== '') $out[] = $s;
    }
    return $out;
}

function cleanPhotos($v): array {
    return array_values(array_filter(cleanList($v, 300, 5), 'own_photo_url'));
}

$order = [
    'lang'      => $lang,
    'source'    => clean($in['source'] ?? '', 40),          // product | builder
    'product'   => clean($in['product'] ?? '', 120),
    'url'       => clean($in['url'] ?? '', 300),
    'size'      => clean($in['size'] ?? '', 80),
    'price'     => clean($in['price'] ?? '', 20),
    'sponge'    => clean($in['sponge'] ?? '', 80),
    'filling'   => clean($in['filling'] ?? '', 80),
    'lettering' => clean($in['lettering'] ?? '', 120),
    'date'      => clean($in['date'] ?? '', 20),
    'time'      => clean($in['time'] ?? '', 30),
    'delivery'  => clean($in['delivery'] ?? '', 40),
    'address'   => clean($in['address'] ?? '', 200),
    'point'     => clean($in['point'] ?? '', 120),
    'name'      => clean($in['name'] ?? '', 80),
    'phone'     => clean($in['phone'] ?? '', 40),
    'recipient' => clean($in['recipient'] ?? '', 120),
    'extras'    => cleanList($in['extras'] ?? [], 200, 8),
    'photos'    => cleanPhotos($in['photos'] ?? []),
];
if ($order['name'] === '' || $order['phone'] === '') bad('fields');

$id = order_add($order);
if ($id === null) bad('io', 500);

$rl['n']++;
@file_put_contents($rlFile, json_encode($rl));

echo json_encode(['ok' => true, 'id' => $id]);
