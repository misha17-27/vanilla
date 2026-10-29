<?php
// Чат на сайте: окно чата (assets/chat.js) присылает сюда ответы клиента, движок
// (includes/bot.php) отвечает следующим вопросом. WhatsApp и Instagram подключаются
// к тому же движку и сценарию — меняется только канал.
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/bot.php';
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

const CHAT_RATE  = 300; // запросов в час с одного IP
const ORDER_RATE = 20;  // заказов в час с одного IP — как у формы заказа
const STALE      = 6 * 3600; // окно открыли через полдня — повторяем вопрос со свежими датами

function bad(string $code, int $http = 400, array $extra = []): never
{
    http_response_code($http);
    echo json_encode(['ok' => false, 'error' => $code] + $extra);
    exit;
}

// Лимит запросов по IP: файл uploads/ratelimit/{prefix}-{хеш}.json, окно — час
function rate_hit(string $prefix, int $limit): bool
{
    $dir = __DIR__ . '/uploads/ratelimit';
    @mkdir($dir, 0775, true);
    $file = $dir . '/' . $prefix . '-' . sha1($_SERVER['REMOTE_ADDR'] ?? '0') . '.json';
    $rl = json_decode((string)@file_get_contents($file), true) ?: ['t' => time(), 'n' => 0];
    if (time() - $rl['t'] > 3600) $rl = ['t' => time(), 'n' => 0];
    if ($rl['n'] >= $limit) return false;
    $rl['n']++;
    @file_put_contents($file, json_encode($rl));
    return true;
}

function str_in(array $in, string $k, int $max): string
{
    $v = $in[$k] ?? '';
    return is_string($v) ? mb_substr($v, 0, $max) : '';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') bad('method', 405);

$raw = file_get_contents('php://input');
if (strlen($raw) > 8000) bad('size');
$in = json_decode($raw, true);
if (!is_array($in)) bad('json');
// Сессия могла истечь, пока окно было открыто: отдаём новый токен, окно повторит запрос.
// Чужой сайт этот ответ прочитать не может — браузер не отдаст его без CORS.
if (!hash_equals($_SESSION['csrf'], str_in($in, 'csrf', 64))) bad('csrf', 403, ['csrf' => $_SESSION['csrf']]);
if (!rate_hit('chat', CHAT_RATE)) bad('rate', 429);

// Номер разговора хранит браузер (localStorage) — по нему окно продолжает начатое
$cid = str_in($in, 'cid', 40);
if (!preg_match('/^[0-9a-f]{32}$/', $cid)) bad('cid');
$pageLang = in_array($in['lang'] ?? '', $LANGS, true) ? $in['lang'] : 'ru';

// Ответ клиента → вход движка. Чужие ключи не пропускаем.
$msg = is_array($in['msg'] ?? null) ? $in['msg'] : ['type' => 'start'];
$type = str_in($msg, 'type', 10);
$input = ['type' => $type];
switch ($type) {
    case 'start':
    case 'restart':
        break;
    case 'human':
    case 'option':
        $input += ['id' => str_in($msg, 'id', 60), 'label' => str_in($msg, 'label', 120)];
        if (isset($msg['step'])) $input['step'] = str_in($msg, 'step', 40);
        break;
    case 'text':
        $input['text'] = str_in($msg, 'text', 1000);
        break;
    case 'photo':
        $url = str_in($msg, 'url', 300);
        if (!own_photo_url($url)) bad('photo');
        $input += ['url' => $url, 'label' => ''];
        break;
    default:
        bad('type');
}

// Капча (если включена в админке) — перед тем, как заказ попадёт в список
if ($type === 'option' && $input['id'] === 'confirm' && !turnstile_ok(str_in($msg, 'captcha', 2048))) {
    bad('captcha', 403);
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$bot = new OrderBot('web', [
    'site'     => $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? parse_url(CANON_HOST, PHP_URL_HOST)),
    'wa'       => WA_NUMBER,
    'schedule' => load_schedule(),
    'order'    => fn(array $order): ?string => rate_hit('ord', ORDER_RATE) ? order_add($order) : null,
]);

[$fh, $state] = bot_open('web', $cid);
$isNew = $state === null;
$state ??= OrderBot::fresh('web', $cid, $pageLang);
$state['lang'] = $pageLang; // на сайте бот говорит на языке страницы

$messages = [];
if ($type !== 'start' || $isNew || !$state['history'] || time() - (int)$state['updated'] > STALE) {
    $messages = $bot->handle($state, $input);
}
bot_save($fh, $state);

$res = ['ok' => true, 'messages' => $messages];
if ($type === 'start' || $type === 'restart') {
    // окно рисует разговор целиком — после перезагрузки страницы он продолжается
    $res['history'] = $state['history'];
}
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
