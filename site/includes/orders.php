<?php
// Заказы в data/orders.json: их пишут форма заказа (order-save.php) и чат-бот, читает админка.

// Добавляет заказ в начало списка и возвращает его номер; null — не удалось записать.
// Пишем с блокировкой — заказы могут прийти одновременно.
function order_add(array $order): ?string
{
    $order = [
        'id'      => date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
        'created' => time(),
        'status'  => 'new',
    ] + $order;

    $file = __DIR__ . '/../data/orders.json';
    @mkdir(dirname($file), 0775, true);
    $fh = fopen($file, 'c+');
    if (!$fh) return null;
    flock($fh, LOCK_EX);
    $data = json_decode((string)stream_get_contents($fh), true) ?: ['orders' => []];
    array_unshift($data['orders'], $order);
    $data['orders'] = array_slice($data['orders'], 0, 2000);
    $data['updated'] = date('Y-m-d H:i');
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $order['id'];
}

// Фото заказа — только наши загрузки (/uploads/designs/design-…jpg на нашем домене):
// в админке они становятся ссылками и картинками, чужой адрес туда не пускаем
function own_photo_url(string $u): bool
{
    $hosts = array_filter([
        strtolower((string)parse_url(CANON_HOST, PHP_URL_HOST)),
        strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''))),
    ]);
    if (!preg_match('~^https?://([a-z0-9.-]+)(?::\d+)?/uploads/designs/design-\d{8}-[0-9a-f]{16}\.jpg$~i', $u, $m)) return false;
    return in_array(strtolower($m[1]), $hosts, true);
}
