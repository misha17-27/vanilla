<?php
// Блог: каждая статья — отдельный JSON-файл {slug}.json.
// В нём общие поля (дата, фото) и тексты на трёх языках: ru / az / en.
// Текст статьи — список блоков (абзац, подзаголовок, список, таблица, карточки
// тортов, приглашение в конструктор…), см. blog_render().
//
// Статьи лежат в двух местах:
//  content/blog/ — из репозитория, при деплое папка заменяется целиком;
//  data/blog/    — созданные и изменённые в админке. Деплой data/ не трогает,
//                  а файл с тем же slug перекрывает версию из репозитория.
require_once __DIR__ . '/config.php';

const BLOG_DIR      = __DIR__ . '/../content/blog';
const BLOG_DATA_DIR = __DIR__ . '/../data/blog';

function blog_read(string $f): ?array
{
    $p = json_decode((string)@file_get_contents($f), true);
    if (!is_array($p) || ($p['slug'] ?? '') !== basename($f, '.json')) return null;
    if (!preg_match('/^[a-z0-9-]+$/', $p['slug'])) return null;
    return $p;
}

// Все статьи, включая черновики. src: repo — из репозитория, override —
// из репозитория, но изменена в админке, admin — создана в админке.
function blog_all(): array
{
    static $all = null;
    if ($all !== null) return $all;
    $all = [];
    foreach (glob(BLOG_DIR . '/*.json') ?: [] as $f) {
        if ($p = blog_read($f)) $all[$p['slug']] = $p + ['_src' => 'repo'];
    }
    foreach (glob(BLOG_DATA_DIR . '/*.json') ?: [] as $f) {
        if (!($p = blog_read($f))) continue;
        $p['_src'] = isset($all[$p['slug']]) ? 'override' : 'admin';
        // исходную статью в коде обновили уже после правки в админке
        $repo = BLOG_DIR . '/' . $p['slug'] . '.json';
        $p['_stale'] = $p['_src'] === 'override' && !empty($p['_base']) && is_file($repo) && sha1_file($repo) !== $p['_base'];
        $all[$p['slug']] = $p;
    }
    uasort($all, fn($a, $b) => strcmp((string)($b['published'] ?? ''), (string)($a['published'] ?? '')) ?: strcmp($a['slug'], $b['slug']));
    return $all;
}

// Языки, на которых статья не готова (нет заголовка или текста)
function blog_missing_langs(array $p): array
{
    global $LANGS;
    return array_values(array_filter($LANGS, fn($l) => trim((string)($p[$l]['title'] ?? '')) === '' || empty($p[$l]['body'])));
}

// Сегодня по бакинскому времени, а не по часовому поясу сервера
function blog_today(): string
{
    return (new DateTime('now', new DateTimeZone('Asia/Baku')))->format('Y-m-d');
}

// Состояние статьи: live — на сайте, draft, scheduled (дата в будущем), incomplete
function blog_state(array $p): string
{
    if (!empty($p['draft'])) return 'draft';
    // Статья выходит, только когда готовы все три языка: иначе hreflang
    // и переключатель языка вели бы на пустые страницы
    if (blog_missing_langs($p)) return 'incomplete';
    if ((string)($p['published'] ?? '9999') > blog_today()) return 'scheduled';
    return 'live';
}

// Все опубликованные статьи, свежие первыми
function blog_posts(): array
{
    static $posts = null;
    if ($posts !== null) return $posts;
    $posts = array_filter(blog_all(), fn($p) => blog_state($p) === 'live');
    return $posts;
}

function blog_post(string $slug): ?array
{
    return blog_posts()[$slug] ?? null;
}

// Поле статьи на текущем языке
function blog_text(array $p, string $key): string
{
    global $lang;
    return trim((string)($p[$lang][$key] ?? ''));
}

function blog_path(array $p): string
{
    return '/blog/' . $p['slug'] . '/';
}

function blog_url(array $p): string
{
    return u(blog_path($p));
}

function blog_updated(array $p): string
{
    return max((string)$p['published'], (string)($p['updated'] ?? ''));
}

// 19 октября 2026 / 19 oktyabr 2026 / 19 October 2026
function blog_date(string $ymd): string
{
    global $t;
    $ts = strtotime($ymd);
    if (!$ts) return '';
    $m = $t['blog_months'][(int)date('n', $ts) - 1] ?? date('m', $ts);
    return (int)date('j', $ts) . ' ' . $m . ' ' . date('Y', $ts);
}

// Число слов в тексте статьи на текущем языке (адреса ссылок не считаем)
function blog_words(array $p): int
{
    global $lang;
    $text = [];
    foreach ($p[$lang]['body'] as $b) {
        foreach (['text', 'title', 'caption'] as $k) $text[] = (string)($b[$k] ?? '');
        foreach (['items', 'head'] as $k) foreach ((array)($b[$k] ?? []) as $v) $text[] = is_array($v) ? implode(' ', $v) : (string)$v;
        foreach ((array)($b['rows'] ?? []) as $row) $text[] = implode(' ', (array)$row);
    }
    $plain = preg_replace('/\[([^\]]+)\]\([^)\s]+\)/u', '$1', implode(' ', $text));
    return count(preg_split('/\s+/u', trim(str_replace('**', '', $plain)), -1, PREG_SPLIT_NO_EMPTY));
}

// Время чтения: ~180 слов в минуту
function blog_minutes(array $p): int
{
    return max(1, (int)round(blog_words($p) / 180));
}

// Строка текста: экранируем, затем **жирный** и [ссылка](/адрес/).
// Внутренние адреса получают приставку языка; «wa» — ссылка в WhatsApp.
function blog_inline(string $s): string
{
    $h = e($s);
    $h = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $h);
    return preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/u', function ($m) {
        $href = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
        if ($href === 'wa') {
            return '<a href="' . e(wa_link()) . '" target="_blank" rel="noopener">' . $m[1] . '</a>';
        }
        if (preg_match('#^/(?!/)[a-z0-9/_-]*$#i', $href)) {
            return '<a href="' . e(u($href)) . '">' . $m[1] . '</a>';
        }
        if (preg_match('#^https://#i', $href)) {
            return '<a href="' . e($href) . '" target="_blank" rel="noopener">' . $m[1] . '</a>';
        }
        return $m[1];
    }, $h);
}

// Ссылка и подпись кнопки для блока «cta»: konstruktor, cat:{ключ категории},
// wa или прямой адрес страницы
function blog_cta_target(string $target): array
{
    global $t;
    if ($target === 'konstruktor') return [u('/konstruktor/'), $t['blog_cta_konstr'], false];
    if ($target === 'wa') return [wa_link(), $t['btn_wa'], true];
    if (str_starts_with($target, 'cat:')) {
        $key = substr($target, 4);
        $c = categories()[$key] ?? null;
        $page = $c['page'] ?? '';
        $path = $page === 'own' ? cat_path($c) : ($page === 'ctg' ? '/bolme/cake-to-go/' : '/bolme/bento-tort/');
        return [u($path), $t['blog_cta_cat'], false];
    }
    if (preg_match('#^/(?!/)[a-z0-9/_-]*$#i', $target)) return [u($target), $t['blog_cta_more'], false];
    return [u('/konstruktor/'), $t['blog_cta_konstr'], false];
}

// Подзаголовки h2 для оглавления
function blog_toc(array $blocks): array
{
    $toc = [];
    foreach ($blocks as $b) {
        if (($b['type'] ?? '') === 'h2') $toc[] = ['part-' . (count($toc) + 1), (string)$b['text']];
    }
    return $toc;
}

// Текст статьи в HTML. Вопросы из блоков «faq» собираются в $faqOut для разметки.
function blog_render(array $blocks, array &$faqOut = []): string
{
    global $PRODUCTS_BY_SLUG;
    $out = '';
    $h2 = 0;
    foreach ($blocks as $b) {
        switch ($b['type'] ?? '') {
            case 'p':
                $out .= '<p>' . blog_inline((string)$b['text']) . '</p>';
                break;
            case 'h2':
                $h2++;
                $out .= '<h2 id="part-' . $h2 . '">' . e((string)$b['text']) . '</h2>';
                break;
            case 'h3':
                $out .= '<h3>' . e((string)$b['text']) . '</h3>';
                break;
            case 'ul':
            case 'ol':
                $tag = $b['type'];
                $out .= '<' . $tag . '>';
                foreach ((array)$b['items'] as $it) $out .= '<li>' . blog_inline((string)$it) . '</li>';
                $out .= '</' . $tag . '>';
                break;
            case 'tip':
                $out .= '<aside class="b-tip">' . blog_inline((string)$b['text']) . '</aside>';
                break;
            case 'table':
                $out .= '<div class="b-table"><table><thead><tr>';
                foreach ((array)$b['head'] as $c) $out .= '<th>' . blog_inline((string)$c) . '</th>';
                $out .= '</tr></thead><tbody>';
                foreach ((array)$b['rows'] as $row) {
                    $out .= '<tr>';
                    foreach ((array)$row as $c) $out .= '<td>' . blog_inline((string)$c) . '</td>';
                    $out .= '</tr>';
                }
                $out .= '</tbody></table></div>';
                break;
            case 'img':
                $out .= '<figure class="b-fig"><img loading="lazy" decoding="async" src="' . e(asset((string)$b['src'])) . '" alt="' . e((string)($b['alt'] ?? '')) . '" width="800" height="800">';
                if (!empty($b['caption'])) $out .= '<figcaption>' . blog_inline((string)$b['caption']) . '</figcaption>';
                $out .= '</figure>';
                break;
            case 'products':
                // карточки тортов — ведут на страницы товаров
                $cards = array_filter(array_map(fn($s) => $PRODUCTS_BY_SLUG[$s] ?? null, (array)$b['slugs']));
                if (!$cards) break;
                ob_start();
                foreach ($cards as $p) product_card($p);
                $out .= '<div class="pgrid b-products">' . ob_get_clean() . '</div>';
                break;
            case 'cta':
                [$href, $btn, $ext] = blog_cta_target((string)($b['target'] ?? 'konstruktor'));
                $btn = trim((string)($b['button'] ?? '')) ?: $btn;
                $out .= '<div class="b-cta"><div class="b-cta-txt">';
                if (!empty($b['title'])) $out .= '<b>' . e((string)$b['title']) . '</b>';
                if (!empty($b['text'])) $out .= '<span>' . blog_inline((string)$b['text']) . '</span>';
                $out .= '</div><a class="btn ' . ($ext ? 'btn-wa' : 'btn-primary') . '" href="' . e($href) . '"'
                      . ($ext ? ' target="_blank" rel="noopener"' : '') . '>' . e($btn) . '</a></div>';
                break;
            case 'faq':
                $out .= '<div class="faq-list b-faq">';
                foreach ((array)$b['items'] as $qa) {
                    $q = (string)($qa['q'] ?? '');
                    $a = (string)($qa['a'] ?? '');
                    if ($q === '' || $a === '') continue;
                    $faqOut[] = [$q, $a];
                    $out .= '<details><summary>' . e($q) . '</summary><div class="a">' . blog_inline($a) . '</div></details>';
                }
                $out .= '</div>';
                break;
        }
    }
    return $out;
}

// Карточка статьи для списка
function blog_card(array $p, bool $lazy = true): string
{
    global $t;
    $url = blog_url($p);
    $title = blog_text($p, 'title');
    return '<article class="b-card reveal">'
        . '<a class="b-card-ph" href="' . e($url) . '" tabindex="-1" aria-hidden="true">'
        . '<img ' . ($lazy ? 'loading="lazy"' : 'fetchpriority="high"') . ' decoding="async" src="' . e(thumb_url((string)$p['image'])) . '" alt="" width="400" height="400"></a>'
        . '<div class="b-card-body">'
        . '<span class="b-meta">' . e(blog_date($p['published'])) . ' · ' . e(sprintf($t['blog_min'], blog_minutes($p))) . '</span>'
        . '<h2><a href="' . e($url) . '">' . e($title) . '</a></h2>'
        . '<p>' . e(blog_text($p, 'excerpt')) . '</p>'
        . '<a class="b-more" href="' . e($url) . '">' . e($t['blog_read']) . ' →</a>'
        . '</div></article>';
}


// ===== Текст статьи в админке =====
// В админке статью правят обычным текстом с простой разметкой, а хранится она
// блоками. Эти две функции переводят одно в другое; разметка:
//   ## Подзаголовок            ### Подзаголовок поменьше
//   Абзац — строки до пустой строки (переносы внутри склеиваются)
//   - пункт списка             1. пункт нумерованного списка
//   > совет в рамке
//   | Ячейка | Ячейка |       таблица: первая строка — шапка, строка |---| можно не писать
//   [товары: slug, slug, slug]
//   [кнопка: konstruktor | Заголовок | Текст | Надпись на кнопке]
//   [фото: /assets/img/... | описание фото | подпись]
//   ? Вопрос                   ответ — на следующих строках; подряд идущие вопросы
//   Ответ                      собираются в один блок «Частые вопросы»
// Внутри текста: **жирный** и [текст ссылки](/адрес/).

function blog_to_text(array $blocks): string
{
    $out = [];
    $faqOpen = false;
    foreach ($blocks as $b) {
        $type = (string)($b['type'] ?? '');
        switch ($type) {
            case 'p':
                $out[] = (string)$b['text'];
                break;
            case 'h2':
                $out[] = '## ' . $b['text'];
                break;
            case 'h3':
                $out[] = '### ' . $b['text'];
                break;
            case 'ul':
                $out[] = implode("\n", array_map(fn($i) => '- ' . $i, (array)$b['items']));
                break;
            case 'ol':
                $n = 0;
                $out[] = implode("\n", array_map(function ($i) use (&$n) { return (++$n) . '. ' . $i; }, (array)$b['items']));
                break;
            case 'tip':
                $out[] = '> ' . $b['text'];
                break;
            case 'table':
                $rows = ['| ' . implode(' | ', (array)$b['head']) . ' |', '|' . str_repeat('---|', count((array)$b['head']))];
                foreach ((array)$b['rows'] as $r) $rows[] = '| ' . implode(' | ', (array)$r) . ' |';
                $out[] = implode("\n", $rows);
                break;
            case 'img':
                $out[] = '[фото: ' . $b['src'] . ' | ' . ($b['alt'] ?? '') . ' | ' . ($b['caption'] ?? '') . ']';
                break;
            case 'products':
                $out[] = '[товары: ' . implode(', ', (array)$b['slugs']) . ']';
                break;
            case 'cta':
                $out[] = '[кнопка: ' . ($b['target'] ?? 'konstruktor') . ' | ' . ($b['title'] ?? '') . ' | ' . ($b['text'] ?? '') . ' | ' . ($b['button'] ?? '') . ']';
                break;
            case 'faq':
                $qa = [];
                foreach ((array)$b['items'] as $it) $qa[] = '? ' . $it['q'] . "\n" . $it['a'];
                $out[] = implode("\n\n", $qa);
                break;
        }
    }
    return implode("\n\n", $out) . "\n";
}

// Обратно: текст из админки в блоки. Неизвестная разметка остаётся обычным абзацем.
function blog_from_text(string $txt): array
{
    $lines = preg_split('/\r\n|\r|\n/', str_replace("\t", ' ', $txt));
    $blocks = [];
    $n = count($lines);
    $i = 0;
    $isSpecial = fn(string $s): bool => (bool)preg_match('/^(#{2,3} |- |\d+\. |> |\||\? |\[(товары|кнопка|фото):)/u', $s);
    while ($i < $n) {
        $line = trim($lines[$i]);
        if ($line === '') { $i++; continue; }

        if (preg_match('/^(#{2,3}) (.+)$/u', $line, $m)) {
            $blocks[] = ['type' => strlen($m[1]) === 2 ? 'h2' : 'h3', 'text' => trim($m[2])];
            $i++;
            continue;
        }
        if (preg_match('/^\[(товары|кнопка|фото):\s*(.*)\]$/u', $line, $m)) {
            $parts = array_map('trim', explode('|', $m[2]));
            if ($m[1] === 'товары') {
                $slugs = array_values(array_filter(array_map('trim', explode(',', $m[2])), fn($s) => $s !== ''));
                $blocks[] = ['type' => 'products', 'slugs' => $slugs];
            } elseif ($m[1] === 'кнопка') {
                $b = ['type' => 'cta', 'target' => $parts[0] !== '' ? $parts[0] : 'konstruktor'];
                if (($parts[1] ?? '') !== '') $b['title'] = $parts[1];
                if (($parts[2] ?? '') !== '') $b['text'] = $parts[2];
                if (($parts[3] ?? '') !== '') $b['button'] = $parts[3];
                $blocks[] = $b;
            } else {
                $b = ['type' => 'img', 'src' => $parts[0], 'alt' => $parts[1] ?? ''];
                if (($parts[2] ?? '') !== '') $b['caption'] = $parts[2];
                $blocks[] = $b;
            }
            $i++;
            continue;
        }
        if (str_starts_with($line, '- ') || preg_match('/^\d+\. /', $line)) {
            $ordered = !str_starts_with($line, '- ');
            $items = [];
            while ($i < $n) {
                $l = trim($lines[$i]);
                if ($ordered && preg_match('/^\d+\. (.*)$/u', $l, $m)) $items[] = trim($m[1]);
                elseif (!$ordered && str_starts_with($l, '- ')) $items[] = trim(substr($l, 2));
                else break;
                $i++;
            }
            $blocks[] = ['type' => $ordered ? 'ol' : 'ul', 'items' => $items];
            continue;
        }
        if (str_starts_with($line, '> ')) {
            $parts = [];
            while ($i < $n && str_starts_with(trim($lines[$i]), '> ')) { $parts[] = trim(substr(trim($lines[$i]), 2)); $i++; }
            $blocks[] = ['type' => 'tip', 'text' => implode(' ', $parts)];
            continue;
        }
        if (str_starts_with($line, '|')) {
            $rows = [];
            while ($i < $n && str_starts_with(trim($lines[$i]), '|')) {
                $l = trim($lines[$i]);
                $i++;
                if (preg_match('/^\|[\s:|-]+\|?$/', $l) && str_contains($l, '-')) continue;   // |---|---|
                $cells = array_map('trim', explode('|', trim($l, '|')));
                $rows[] = $cells;
            }
            if ($rows) $blocks[] = ['type' => 'table', 'head' => array_shift($rows), 'rows' => $rows];
            continue;
        }
        if (str_starts_with($line, '? ')) {
            $items = [];
            while ($i < $n) {
                $l = trim($lines[$i]);
                if ($l === '') {
                    // пустая строка: вопросы продолжаются, если дальше снова «? »
                    $j = $i;
                    while ($j < $n && trim($lines[$j]) === '') $j++;
                    if ($j < $n && str_starts_with(trim($lines[$j]), '? ')) { $i = $j; continue; }
                    break;
                }
                if (!str_starts_with($l, '? ')) break;
                $q = trim(substr($l, 2));
                $i++;
                $ans = [];
                while ($i < $n && trim($lines[$i]) !== '' && !str_starts_with(trim($lines[$i]), '? ')) { $ans[] = trim($lines[$i]); $i++; }
                $items[] = ['q' => $q, 'a' => implode(' ', $ans)];
            }
            $blocks[] = ['type' => 'faq', 'items' => $items];
            continue;
        }
        // абзац: до пустой строки или до строки с разметкой
        $parts = [];
        while ($i < $n) {
            $l = trim($lines[$i]);
            if ($l === '' || ($parts && $isSpecial($l))) break;
            $parts[] = $l;
            $i++;
        }
        $blocks[] = ['type' => 'p', 'text' => implode(' ', $parts)];
    }
    return $blocks;
}
