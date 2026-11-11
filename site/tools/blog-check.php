<?php
// Проверка статей блога перед публикацией (инструмент разработки, на сайт не копируется).
//   php tools/blog-check.php slug1 slug2 ...   — только эти статьи (иначе все)
//   --paths — показать адреса, на которые можно ссылаться
//   --http  — ещё открыть страницы на локальном сервере http://localhost:8123 (должен быть запущен)
// Ошибки (ERR) — исправить обязательно; WARN — посмотреть глазами.
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$FORCE_LANG = 'ru';
require __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/blog.php';

$args = array_slice($argv, 1);
$http = in_array('--http', $args, true);
$want = array_values(array_filter($args, fn($a) => $a[0] !== '-'));
$SITE = dirname(__DIR__);
$BASE = 'http://localhost:8123';
$LANGS3 = ['ru', 'az', 'en'];
$PREFIX = ['ru' => '', 'az' => '/az', 'en' => '/en'];

$all = [];
foreach (glob($SITE . '/content/blog/*.json') as $f) {
    $p = json_decode((string)file_get_contents($f), true);
    $all[basename($f, '.json')] = is_array($p) ? $p : null;
}
$cats = categories();
// Все адреса сайта, на которые можно ссылаться из статьи
$PATHS = array_fill_keys(['/', '/bolme/bento-tort/', '/bolme/cake-to-go/', '/terkibler/', '/reyler/', '/konstruktor/', '/haqqimizda/', '/faq/', '/elaqe/', '/blog/'], 1);
foreach ($cats as $c) if (($c['page'] ?? '') === 'own') $PATHS[cat_path($c)] = 1;
foreach ($PRODUCTS_BY_SLUG as $s => $_) $PATHS["/mehsul/$s/"] = 1;
foreach ($all as $s => $_) $PATHS["/blog/$s/"] = 1;
// статьи, которые ещё пишутся (по slug на строку в tools/blog-planned.txt), — на них уже можно ссылаться
foreach (is_file(__DIR__ . '/blog-planned.txt') ? file(__DIR__ . '/blog-planned.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $s) $PATHS['/blog/' . trim($s) . '/'] = 1;
if (in_array('--paths', $argv, true)) { echo implode("\n", array_keys(array_filter($PATHS, fn($k) => !str_starts_with($k, '/mehsul/'), ARRAY_FILTER_USE_KEY))), "\n"; exit; }

// Допустимые цены — те, что есть на сайте (тексты и каталог)
$prices = [];
foreach ($LANGS3 as $l) {
    $tt = require $SITE . "/lang/$l.php";
    array_walk_recursive($tt, function ($v) use (&$prices) {
        if (is_string($v) && preg_match_all('/(\d+)\s*(?:–\s*\d+\s*)?(?:₼|AZN|manat)/u', $v, $m)) foreach ($m[1] as $n) $prices[(int)$n] = 1;
        if (is_string($v) && preg_match_all('/(\d+)\s*–\s*(\d+)\s*(?:₼|AZN)/u', $v, $m)) foreach ($m[2] as $n) $prices[(int)$n] = 1;
    });
}
foreach ($PRODUCTS_BY_SLUG as $pp) {
    if (preg_match_all('/\d+/', (string)($pp['price'] ?? ''), $m)) foreach ($m[0] as $n) $prices[(int)$n] = 1;
    foreach (['seo_desc'] as $k) if (preg_match_all('/(\d+)\s*₼/u', (string)($pp[$k] ?? ''), $m)) foreach ($m[1] as $n) $prices[(int)$n] = 1;
}

$BAD = [
    'ru' => '/оплат|предоплат|наличн|банковск|карт(ой|ы|у)\b|перевод(ом)?\s+на|скидк|акци[яию]|промокод|хран(ить|ение|ится|ите)|холодильник|срок\w* годност|бесплатн\w* доставк/iu',
    'az' => '/ödəniş|ön ödəniş|nağd|bank kart|endirim|aksiya|promokod|saxla(yın|nır|maq|nılır)|soyuducu|yararlılıq|pulsuz çatdırılma/iu',
    'en' => '/payment|prepay|\bcash\b|credit card|bank card|discount|promo code|\bsale\b|\bstor(e|age|ing)\b|fridge|refrigerat|shelf life|free delivery/iu',
];

function texts_of(array $body): array
{
    $out = [];
    foreach ($body as $b) {
        foreach (['text', 'title', 'caption', 'alt', 'button'] as $k) if (isset($b[$k])) $out[] = (string)$b[$k];
        foreach ((array)($b['items'] ?? []) as $it) {
            if (is_array($it)) { $out[] = (string)($it['q'] ?? ''); $out[] = (string)($it['a'] ?? ''); } else $out[] = (string)$it;
        }
        foreach ((array)($b['head'] ?? []) as $c) $out[] = (string)$c;
        foreach ((array)($b['rows'] ?? []) as $r) foreach ((array)$r as $c) $out[] = (string)$c;
    }
    return $out;
}
function norm_blocks(array $bs): string
{
    $bs = array_map(function ($b) {
        foreach (['title', 'text', 'button', 'caption'] as $k) if (isset($b[$k]) && $b[$k] === '' && in_array($b['type'], ['cta', 'img'], true)) unset($b[$k]);
        if (($b['type'] ?? '') === 'img' && !isset($b['alt'])) $b['alt'] = '';
        ksort($b);
        return $b;
    }, $bs);
    return json_encode($bs, JSON_UNESCAPED_UNICODE);
}
function get(string $url): array
{
    $ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 20, 'follow_location' => 0]]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach ($http_response_header ?? [] as $h) if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) $code = (int)$m[1];
    return [$code, (string)$body];
}

$targets = $want ?: array_keys($all);
$errs = 0; $warns = 0; $pathCache = [];
foreach ($targets as $slug) {
    $E = []; $W = [];
    $p = $all[$slug] ?? null;
    if (!$p) { echo "== $slug\n  ERR file missing or invalid JSON\n"; $errs++; continue; }
    if (($p['slug'] ?? '') !== $slug) $E[] = 'slug field must equal file name';
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) $E[] = 'slug: only a-z 0-9 -';
    foreach (['published', 'updated'] as $k) if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($p[$k] ?? ''))) $E[] = "$k must be YYYY-MM-DD";
    $img = (string)($p['image'] ?? '');
    if (!preg_match('#^/assets/img/#', $img) || !is_file($SITE . $img)) $E[] = "cover image not found: $img";
    foreach ($LANGS3 as $l) {
        $L = $p[$l] ?? null;
        if (!is_array($L)) { $E[] = "[$l] missing"; continue; }
        $title = trim((string)($L['title'] ?? ''));
        $st = (string)($L['seo_title'] ?? '');
        $sd = (string)($L['seo_desc'] ?? '');
        if ($title === '') $E[] = "[$l] title empty";
        if (mb_strlen($title) > 90) $W[] = "[$l] title long (" . mb_strlen($title) . ')';
        if (!str_ends_with($st, ' | Vanilla Cake')) $E[] = "[$l] seo_title must end with ' | Vanilla Cake'";
        if (mb_strlen($st) > 60) $E[] = "[$l] seo_title " . mb_strlen($st) . ' chars (max 60)';
        if (mb_strlen($sd) < 130 || mb_strlen($sd) > 160) $E[] = "[$l] seo_desc " . mb_strlen($sd) . ' chars (need 130–160)';
        if (trim((string)($L['excerpt'] ?? '')) === '') $E[] = "[$l] excerpt empty";
        elseif (mb_strlen($L['excerpt']) > 230) $W[] = "[$l] excerpt long (" . mb_strlen($L['excerpt']) . ')';
        if (trim((string)($L['image_alt'] ?? '')) === '') $E[] = "[$l] image_alt empty";
        $body = $L['body'] ?? [];
        if (!is_array($body) || count($body) < 12) { $E[] = "[$l] body too short (" . (is_array($body) ? count($body) : 0) . ' blocks)'; continue; }
        if (($body[0]['type'] ?? '') !== 'p') $W[] = "[$l] first block should be a paragraph (intro)";
        $h2 = 0; $hasProducts = false; $hasCta = false; $hasFaq = false;
        foreach ($body as $i => $b) {
            $ty = $b['type'] ?? '';
            switch ($ty) {
                case 'p': case 'h2': case 'h3': case 'tip':
                    if (trim((string)($b['text'] ?? '')) === '') $E[] = "[$l] #$i $ty empty";
                    if ($ty === 'h2') $h2++;
                    break;
                case 'ul': case 'ol':
                    if (!$b['items'] ?? null) $E[] = "[$l] #$i list empty";
                    break;
                case 'table':
                    $n = count((array)($b['head'] ?? []));
                    if (!$n || !($b['rows'] ?? [])) $E[] = "[$l] #$i table empty";
                    foreach ((array)($b['rows'] ?? []) as $r) if (count((array)$r) !== $n) $E[] = "[$l] #$i table row has " . count((array)$r) . " cells, head $n";
                    break;
                case 'img':
                    if (!is_file($SITE . (string)($b['src'] ?? ''))) $E[] = "[$l] #$i img not found: " . ($b['src'] ?? '');
                    if (trim((string)($b['alt'] ?? '')) === '') $E[] = "[$l] #$i img alt empty";
                    break;
                case 'products':
                    $hasProducts = true;
                    foreach ((array)($b['slugs'] ?? []) as $s) if (!isset($PRODUCTS_BY_SLUG[$s])) $E[] = "[$l] #$i product not in catalog: $s";
                    $c = count((array)($b['slugs'] ?? []));
                    if ($c < 2 || $c > 8) $W[] = "[$l] #$i products: $c cards (2–8 look best)";
                    break;
                case 'cta':
                    $hasCta = true;
                    $tg = (string)($b['target'] ?? '');
                    if (str_starts_with($tg, 'cat:')) { if (!isset($cats[substr($tg, 4)])) $E[] = "[$l] #$i cta unknown category $tg"; }
                    elseif (!in_array($tg, ['konstruktor', 'wa'], true) && !isset($PATHS[$tg])) $E[] = "[$l] #$i cta bad target $tg";
                    if (trim((string)($b['title'] ?? '')) === '' && trim((string)($b['text'] ?? '')) === '') $E[] = "[$l] #$i cta without text";
                    break;
                case 'faq':
                    $hasFaq = true;
                    foreach ((array)($b['items'] ?? []) as $qa) if (trim((string)($qa['q'] ?? '')) === '' || trim((string)($qa['a'] ?? '')) === '') $E[] = "[$l] #$i faq item empty";
                    break;
                default:
                    $E[] = "[$l] #$i unknown block type '$ty'";
            }
        }
        if ($h2 < 3) $W[] = "[$l] only $h2 h2 headings";
        if (!$hasProducts) $W[] = "[$l] no products block";
        if (!$hasCta) $W[] = "[$l] no cta block";
        if (!$hasFaq) $W[] = "[$l] no faq block";
        // слова
        global $lang;
        $lang = $l;
        $words = blog_words($p);
        if ($words < 600) $W[] = "[$l] $words words (aim 800+)";
        // запрещённые темы
        $txt = implode("\n", array_merge([$title, $st, $sd, (string)$L['excerpt'], (string)$L['image_alt']], texts_of($body)));
        if (preg_match_all($BAD[$l], $txt, $m)) $W[] = "[$l] check wording (payment/discount/storage?): " . implode(', ', array_unique($m[0]));
        // цены
        if (preg_match_all('/(\d+)\s*(?:₼|AZN|manat)/u', $txt, $m)) foreach (array_unique($m[1]) as $n) if (!isset($prices[(int)$n])) $E[] = "[$l] price $n ₼ is not on the site";
        // ссылки в тексте
        preg_match_all('/\[([^\]]+)\]\(([^)\s]+)\)/u', $txt, $m);
        foreach ($m[2] as $href) {
            if ($href === 'wa') continue;
            if (preg_match('#^https://#', $href)) { $W[] = "[$l] external link $href"; continue; }
            if (!preg_match('#^/(?!/)[a-z0-9/_-]*$#', $href)) { $E[] = "[$l] bad link $href"; continue; }
            if (preg_match('#^/(az|en)/#', $href)) $E[] = "[$l] link must not contain language prefix: $href";
            if (!str_ends_with($href, '/')) $W[] = "[$l] link without trailing slash: $href";
            if (!isset($PATHS[$href])) $E[] = "[$l] link to a page that does not exist: $href (run with --paths to list pages)";
            if ($http) {
                $u = $BASE . $PREFIX[$l] . $href;
                $pathCache[$u] ??= get($u)[0];
                if ($pathCache[$u] !== 200) $E[] = "[$l] link $href -> HTTP {$pathCache[$u]}";
            }
        }
        if (preg_match_all('#\]\((/blog/[a-z0-9-]+/)\)#', $txt, $m) && in_array("/blog/$slug/", $m[1], true)) $W[] = "[$l] links to itself";
        // редактор в админке: разметка и визуальный режим туда-обратно без потерь
        if (norm_blocks(blog_from_text(blog_to_text($body))) !== norm_blocks($body)) $E[] = "[$l] text-editor roundtrip changes the body (check ** and [] markup)";
        if (norm_blocks(blog_from_html(blog_to_html($body))) !== norm_blocks($body)) $E[] = "[$l] visual-editor roundtrip changes the body";
    }
    // уникальность заголовков
    foreach ($all as $os => $op) {
        if ($os === $slug || !$op) continue;
        foreach ($LANGS3 as $l) {
            if (($op[$l]['seo_title'] ?? null) === ($p[$l]['seo_title'] ?? '')) $E[] = "[$l] seo_title duplicates $os";
            if (($op[$l]['title'] ?? null) === ($p[$l]['title'] ?? '')) $E[] = "[$l] title duplicates $os";
        }
    }
    if ($http) {
        foreach ($LANGS3 as $l) {
            [$code, $html] = get($BASE . $PREFIX[$l] . "/blog/$slug/");
            if ($code !== 200) { $E[] = "[$l] page HTTP $code (published date in the future?)"; continue; }
            if (!str_contains($html, '<h1') || !str_contains($html, htmlspecialchars((string)$p[$l]['title'], ENT_QUOTES))) $E[] = "[$l] page has no h1 with the title";
            if (!str_contains($html, 'BlogPosting')) $E[] = "[$l] page has no BlogPosting schema";
        }
        [$code, $sm] = get($BASE . '/sitemap.xml');
        if (!str_contains($sm, "/blog/$slug/")) $E[] = 'not in sitemap.xml';
    }
    echo "== $slug: " . (count($E) ? count($E) . ' ERR' : 'ok') . (count($W) ? ', ' . count($W) . ' WARN' : '') . "\n";
    foreach ($E as $x) echo "  ERR  $x\n";
    foreach ($W as $x) echo "  WARN $x\n";
    $errs += count($E); $warns += count($W);
}
echo "TOTAL: $errs errors, $warns warnings\n";
exit($errs ? 1 : 0);
