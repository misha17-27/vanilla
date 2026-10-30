<?php
// Чат-бот заказа. Один сценарий — data/bot-scenario.json — для сайта, WhatsApp и Instagram.
// Движок не знает, откуда пришло сообщение: канал передаёт ему ответ клиента и получает
// обратно сообщения в общем виде — текст, варианты ответа, какой ввод ждём. Рисует их
// уже сам канал: кнопками на сайте, кнопками или списком в WhatsApp, quick replies в Instagram.
declare(strict_types=1);

const BOT_SCENARIO_FILE = __DIR__ . '/../data/bot-scenario.json';
const BOT_STATE_DIR     = __DIR__ . '/../data/bot';
const BOT_TZ            = 'Asia/Baku';
const BOT_IDLE_RESET    = 7 * 86400; // неделю не отвечали — даты и ответы устарели, начинаем заново
const BOT_HUMAN_PAUSE   = 86400;     // после «оператор» бот сутки молчит: отвечает человек
const BOT_HISTORY       = 80;        // сколько сообщений разговора храним
const BOT_DATE_BUTTONS  = 6;         // ближайших свободных дней кнопками

function bot_scenario(): array
{
    static $sc = null;
    return $sc ??= json_decode((string)@file_get_contents(BOT_SCENARIO_FILE), true) ?: ['texts' => [], 'steps' => []];
}

// Разговор хранится в data/bot/{канал}-{хеш}.json и открывается с блокировкой: в WhatsApp
// два сообщения подряд приходят почти одновременно, без неё второй ответ затёр бы первый.
// Возвращает [дескриптор, состояние] — null вместо состояния, если разговора ещё не было.
function bot_open(string $channel, string $user): array
{
    @mkdir(BOT_STATE_DIR, 0775, true);
    $fh = fopen(BOT_STATE_DIR . '/' . $channel . '-' . sha1($user) . '.json', 'c+');
    if (!$fh) throw new RuntimeException('bot state');
    flock($fh, LOCK_EX);
    $state = json_decode((string)stream_get_contents($fh), true);
    if (!is_array($state) && random_int(1, 50) === 1) bot_cleanup();
    return [$fh, is_array($state) ? $state : null];
}

function bot_save($fh, array $state): void
{
    $state['updated'] = time();
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
}

// Разговоры, к которым два месяца не возвращались, удаляем: заказы уже в orders.json
function bot_cleanup(): void
{
    foreach (glob(BOT_STATE_DIR . '/*.json') ?: [] as $f) {
        if (filemtime($f) < time() - 60 * 86400) @unlink($f);
    }
}

final class OrderBot
{
    private array $sc;
    private array $st = [];
    private array $out = [];
    private DateTimeZone $tz;
    private DateTimeImmutable $today;

    /**
     * $channel — web | whatsapp | instagram.
     * $env: site — адрес сайта для ссылок, wa — номер WhatsApp, schedule — выходные (load_schedule()),
     *       order — function (array $order): ?string — сохраняет заказ и возвращает его номер.
     */
    public function __construct(private string $channel, private array $env)
    {
        $this->sc    = bot_scenario();
        $this->tz    = new DateTimeZone(BOT_TZ);
        $this->today = new DateTimeImmutable('today', $this->tz);
    }

    public static function fresh(string $channel, string $user, ?string $lang): array
    {
        return [
            'channel' => $channel,
            'user'    => $user,
            'lang'    => $lang,
            'answers' => [],
            'photos'  => [],
            'asked'   => null,     // id шага, который сейчас задан клиенту
            'status'  => 'active', // active | human (отвечает человек) | done (заказ оформлен)
            'paused'  => 0,
            'order'   => null,
            'created' => time(),
            'updated' => time(),
            'history' => [],
        ];
    }

    /**
     * Ответ клиента → сообщения бота. $in:
     *   ['type' => 'start']                         — открыл чат / написал впервые
     *   ['type' => 'option', 'id' => …, 'step' => …] — нажал кнопку варианта
     *   ['type' => 'text', 'text' => …]              — написал текст
     *   ['type' => 'photo', 'url' => …]              — прислал фото (адрес уже проверен каналом)
     *   ['type' => 'restart'] / ['type' => 'human']  — начать заново / позвать человека
     * Каждое сообщение: text, options [[id, label, url?]], step, input (text|phone|photo|none), placeholder,
     * open — ссылка, которую канал открывает сразу, не дожидаясь нажатия (WhatsApp после заказа на сайте).
     */
    public function handle(array &$state, array $in): array
    {
        $this->st  = $state;
        $this->out = [];
        $kind = (string)($in['type'] ?? 'start');

        if (time() - (int)$this->st['updated'] > BOT_IDLE_RESET) $this->reset(true);
        if (!in_array($kind, ['start', 'restart'], true)) $this->logUser($in);

        $cmd = $kind === 'text' ? $this->command((string)($in['text'] ?? '')) : null;
        if ($kind === 'restart' || $cmd === 'restart') {
            $this->reset($kind !== 'restart');
            $kind = 'start';
        }

        if ($kind === 'human' || $cmd === 'human') {
            $this->human();
        } elseif ($this->st['status'] === 'human' && $this->st['paused'] > time()) {
            // разговор ведёт человек — бот не вмешивается
        } elseif ($this->st['status'] === 'done') {
            $this->afterDone($in);
        } elseif ($kind === 'start' || $this->st['asked'] === null || $this->st['status'] === 'human') {
            // первое сообщение («Здравствуйте») — не ответ на вопрос: здороваемся и спрашиваем
            if ($this->st['asked'] === null) $this->say($this->tx('hello'));
            $this->st['status'] = 'active';
            $this->askCurrent();
        } else {
            $this->reply($in);
        }

        $state = $this->st;
        return $this->out;
    }

    // ===== Ход разговора =====

    private function reply(array $in): void
    {
        if ($this->st['asked'] === 'summary:edit') {
            $this->pickEdit($in);
            return;
        }
        $step = $this->current();
        // нажали кнопку под старым вопросом выше в переписке — повторяем текущий
        if ($this->st['asked'] !== $step['id'] || (isset($in['step']) && $in['step'] !== $step['id'])) {
            $this->askCurrent();
            return;
        }
        if ($step['type'] === 'summary') {
            $this->onSummary($step, $in);
            return;
        }
        $err = $this->take($step, $in);
        if ($err === false) return;
        if ($err === null) {
            $this->askCurrent();
            return;
        }
        if ($err !== '') $this->say($err);
        $this->ask($step);
    }

    // Первый шаг, на который ещё нет (или уже нет — сменили бисквит, и начинка не подходит)
    // подходящего ответа. Сводка в конце — всегда.
    private function current(): array
    {
        foreach ($this->sc['steps'] as $s) {
            if (!$this->applies($s)) continue;
            if ($s['type'] === 'summary' || !$this->answered($s)) return $s;
        }
        return end($this->sc['steps']);
    }

    private function askCurrent(): void
    {
        $this->ask($this->current());
    }

    private function applies(array $s): bool
    {
        if (in_array($this->channel, $s['skip_channels'] ?? [], true)) return false;
        return $this->matches($s['if'] ?? []);
    }

    // Условие if шага или варианта: {"type": "bento"} — только если раньше выбрали этот ответ
    private function matches(array $if): bool
    {
        foreach ($if as $k => $want) {
            if (!in_array($this->st['answers'][$k] ?? null, (array)$want, true)) return false;
        }
        return true;
    }

    private function answered(array $s): bool
    {
        if ($s['id'] === 'lang') return $this->st['lang'] !== null;
        if (!array_key_exists($s['id'], $this->st['answers'])) return false;
        $v = $this->st['answers'][$s['id']];
        switch ($s['type']) {
            case 'choice': return $this->find($this->options($s), $v) !== null;
            case 'time':   return $this->find($this->slots(), $v) !== null;
            case 'date':
                $d = $this->ymd((string)$v);
                return $d !== null && $this->dateProblem($d) === null;
            default:       return true;
        }
    }

    private function ask(array $s): void
    {
        $this->st['asked'] = $s['id'];
        $text  = $this->tr($s['ask'] ?? '');
        $input = 'text';
        $opts  = [];
        switch ($s['type']) {
            case 'choice':
                $opts = $this->options($s);
                break;
            case 'text':
                $opts = isset($s['skip']) ? [$s['skip']] : [];
                break;
            case 'photo':
                $opts  = isset($s['skip']) ? [$s['skip']] : [];
                $input = 'photo';
                break;
            case 'phone':
                $input = 'phone';
                break;
            case 'date':
                $opts = $this->dateOptions();
                break;
            case 'time':
                $opts = $this->slots();
                $d = $this->ymd((string)($this->st['answers']['date'] ?? ''));
                if ($d && $d->format('w') === '1') $text .= "\n\n" . $this->tx('monday_note');
                break;
            case 'summary':
                $this->askSummary($s);
                return;
        }
        $this->say($text, $this->labels($opts), $input, $s);
    }

    // Принимает ответ на шаг: null — принят, строка — что сказать перед повтором вопроса,
    // false — бот уже ответил, а вопрос остаётся открытым (его кнопки выше ещё живые)
    private function take(array $s, array $in): string|false|null
    {
        $id   = $s['id'];
        $kind = (string)($in['type'] ?? '');
        $text = $kind === 'text' ? $this->clean((string)($in['text'] ?? '')) : '';

        switch ($s['type']) {
            case 'choice':
                $o = $this->pick($this->options($s), $in);
                if (!$o) return $this->tx('pick_option');
                if ($id === 'lang') {
                    $this->st['lang'] = (string)$o['id'];
                    return null;
                }
                if (isset($o['say'])) {
                    $this->say($this->tr($o['say']), isset($o['link']) ? [$this->linkOption($o)] : []);
                }
                // вариант-ссылка (конструктор): ответ не записываем и вопрос не повторяем
                if (($o['store'] ?? true) === false) return false;
                $this->st['answers'][$id] = (string)$o['id'];
                return null;

            case 'text':
                if ($this->isSkip($s, $in)) {
                    $this->st['answers'][$id] = '';
                    return null;
                }
                if ($kind !== 'text') return $this->tx('pick_option');
                if (mb_strlen($text) < ($s['min'] ?? 1)) return $this->tx('too_short');
                if (mb_strlen($text) > ($s['max'] ?? 200)) return str_replace('{max}', (string)($s['max'] ?? 200), $this->tx('too_long'));
                $this->st['answers'][$id] = $text;
                return null;

            case 'phone':
                $digits = preg_replace('/\D/', '', $text);
                $len = strlen($digits);
                // местные номера без кода страны: 55 123 45 67 и 055 123 45 67
                if ($len === 9 && $digits[0] !== '0') $digits = '994' . $digits;
                elseif ($len === 10 && $digits[0] === '0') $digits = '994' . substr($digits, 1);
                elseif (str_starts_with($digits, '994') ? $len !== 12 : !(str_starts_with($text, '+') && $len >= 10 && $len <= 15)) {
                    return $this->tx('bad_phone'); // иностранный номер — только с «+» и кодом страны
                }
                $this->st['answers'][$id] = '+' . $digits;
                return null;

            case 'photo':
                if ($kind === 'photo' && ($in['url'] ?? '') !== '') {
                    $this->st['answers'][$id] = (string)$in['url'];
                    $this->st['photos'] = [(string)$in['url']];
                    return null;
                }
                if ($this->isSkip($s, $in)) {
                    $this->st['answers'][$id] = '';
                    return null;
                }
                if (preg_match('~^https?://\S+$~i', $text)) { // ссылка на Pinterest, Instagram и т.п.
                    $this->st['answers'][$id] = mb_substr($text, 0, 300);
                    return null;
                }
                return $this->tx('photo_hint');

            case 'date':
                $d = null;
                if ($kind === 'option') $d = $this->ymd((string)($in['id'] ?? ''));
                elseif ($kind === 'text') $d = $this->parseDate($text);
                if (!$d) return $this->tx('bad_date');
                if ($p = $this->dateProblem($d)) return $p;
                $this->st['answers'][$id] = $d->format('Y-m-d');
                return null;

            case 'time':
                $slots = $this->slots();
                $o = $this->pick($slots, $in, false);
                // «15», «15:00», «в 15» — слот, который начинается в этот час
                if (!$o && preg_match('/(?<!\d)(\d{1,2})(?:[:.]\d{2})?(?!\d)/', $text, $m)) {
                    foreach ($slots as $sl) {
                        if ((int)$sl['id'] === (int)$m[1]) $o = $sl;
                    }
                }
                if (!$o) return $this->tx('pick_option');
                $this->st['answers'][$id] = (string)$o['id'];
                return null;
        }
        return $this->tx('pick_option');
    }

    // ===== Сводка, правки, оформление =====

    private function askSummary(array $s): void
    {
        $text = $this->tx('summary') . "\n\n" . implode("\n", array_map(fn($l) => '• ' . $l, $this->lines()));
        if ($p = $this->price()) $text .= "\n\n" . $this->tx('price') . ': ' . $p . ' ₼';
        $text .= "\n\n" . $this->tx('confirm_note');
        $this->say($text, $this->labels($this->summaryOptions($s)), 'none', $s);
    }

    private function summaryOptions(array $s): array
    {
        return [['id' => 'confirm', 'label' => $s['confirm']], ['id' => 'edit', 'label' => $s['edit']]];
    }

    private function onSummary(array $s, array $in): void
    {
        $o = $this->pick($this->summaryOptions($s), $in);
        if (!$o) {
            $this->say($this->tx('pick_option'));
            $this->askSummary($s);
            return;
        }
        if ($o['id'] === 'edit') {
            $this->st['asked'] = 'summary:edit';
            $this->say($this->tx('edit_ask'), $this->labels($s['edit_groups'] ?? []), 'none', ['id' => 'summary:edit']);
            return;
        }
        $this->confirm();
    }

    // «Изменить» → выбрали группу шагов: стираем их ответы, и разговор сам вернётся к ним
    private function pickEdit(array $in): void
    {
        $sum = end($this->sc['steps']);
        $g = $this->pick($sum['edit_groups'] ?? [], $in);
        if ($g) {
            foreach ($g['steps'] as $id) {
                unset($this->st['answers'][$id]);
                if (($this->step($id)['type'] ?? '') === 'photo') $this->st['photos'] = [];
            }
        }
        $this->askCurrent();
    }

    private function confirm(): void
    {
        $id = ($this->env['order'])($this->order());
        if ($id === null) {
            $this->say($this->tx('order_failed'));
            $this->askCurrent();
            return;
        }
        $this->st['status'] = 'done';
        $this->st['order']  = $id;
        $this->st['asked']  = null;
        if ($this->channel === 'web') {
            // на сайте заказ ещё и уходит в WhatsApp — так мы его подтверждаем, как и форму заказа.
            // WhatsApp открывается сразу; кнопка остаётся — на случай, если браузер его не открыл
            $wa = $this->waLink(true);
            $this->say(str_replace('{id}', $id, $this->tx('done_web')), [['id' => 'wa', 'label' => $this->tx('send_wa'), 'url' => $wa]]);
            $this->openNow($wa);
            // дата торта — сайт через день после неё попросит отзыв в Google (assets/site.js)
            $cake = $this->ymd((string)($this->st['answers']['date'] ?? ''));
            if ($cake) $this->out[array_key_last($this->out)]['cake'] = $cake->format('Y-m-d');
        } else {
            $this->say(str_replace('{id}', $id, $this->tx('done')));
        }
    }

    private function afterDone(array $in): void
    {
        if (($in['type'] ?? '') === 'option' && ($in['id'] ?? '') === 'new') {
            $this->reset(true);
            $this->st['asked'] = 'new'; // без повторного приветствия
            $this->askCurrent();
            return;
        }
        $opts = [['id' => 'new', 'label' => $this->tx('new_order')]];
        if ($this->channel === 'web') $opts[] = ['id' => 'wa', 'label' => $this->tx('send_wa'), 'url' => $this->waLink(true)];
        $this->say(str_replace('{id}', (string)$this->st['order'], $this->tx('after_done')), $opts);
    }

    private function human(): void
    {
        if ($this->channel === 'web') {
            // на сайте человек отвечает в WhatsApp — отправляем туда со всем, что уже выбрано
            $wa = $this->waLink(false);
            $this->say($this->tx('human_web'), [['id' => 'wa', 'label' => $this->tx('write_wa'), 'url' => $wa]]);
            $this->openNow($wa);
            return;
        }
        $this->st['status'] = 'human';
        $this->st['paused'] = time() + BOT_HUMAN_PAUSE;
        $this->say($this->tx('human'));
    }

    private function reset(bool $keepHistory): void
    {
        $history  = $keepHistory ? $this->st['history'] : [];
        $this->st = ['history' => $history] + self::fresh($this->st['channel'], $this->st['user'], $this->st['lang']);
    }

    // Заказ в том же виде, что пишет форма на сайте (order-save.php) — админка показывает
    // его так же. Значения — по-русски, как остальная админка; свободный текст — как написал клиент.
    private function order(): array
    {
        $a  = $this->st['answers'];
        $ru = function (string $id): string {
            $s = $this->step($id);
            return $s && $this->applies($s) && array_key_exists($id, $this->st['answers']) ? $this->value($s, 'ru') : '';
        };
        $photo  = $this->applies($this->step('design_photo') ?? []) ? (string)($a['design_photo'] ?? '') : '';
        $photos = $photo !== '' && in_array($photo, $this->st['photos'], true) ? [$photo] : [];

        $extras = [];
        foreach (['design', 'design_photo', 'design_text'] as $id) {
            $s = $this->step($id);
            if (!$s || ($s['type'] === 'photo' && $photos)) continue; // своё фото уходит в галерею заказа
            if (($v = $ru($id)) !== '') $extras[] = $this->tr($s['title'], 'ru') . ': ' . $v;
        }

        $phone = $ru('phone');
        if ($phone === '' && $this->channel === 'whatsapp') $phone = '+' . preg_replace('/\D/', '', $this->st['user']);
        $date = $this->ymd((string)($a['date'] ?? ''));

        return [
            'lang'      => $this->st['lang'] ?? 'ru',
            'source'    => 'chat-' . $this->channel,
            'product'   => $ru('type'),
            'url'       => '',
            'size'      => $ru('size'),
            'price'     => ($p = $this->price()) ? $p . ' AZN' : '', // как у формы заказа (site.js)
            'sponge'    => $ru('sponge'),
            'filling'   => $ru('filling'),
            'lettering' => $ru('lettering'),
            'date'      => $date ? $date->format('d.m.Y') : '',
            'time'      => $ru('time'),
            'delivery'  => $ru('delivery'),
            'address'   => $ru('address'),
            'point'     => '',
            'name'      => $ru('name'),
            'phone'     => $phone,
            'recipient' => '',
            'extras'    => $extras,
            'photos'    => $photos,
        ];
    }

    // Строки «Шаг: ответ» для сводки и сообщения в WhatsApp
    private function lines(?string $lang = null, bool $links = false): array
    {
        $out = [];
        foreach ($this->sc['steps'] as $s) {
            if (!isset($s['title']) || !$this->applies($s) || !array_key_exists($s['id'], $this->st['answers'])) continue;
            $v = $this->value($s, $lang);
            if ($links && $s['type'] === 'photo' && $this->st['answers'][$s['id']] !== '') $v = (string)$this->st['answers'][$s['id']];
            if ($v !== '') $out[] = $this->tr($s['title'], $lang) . ': ' . $v;
        }
        return $out;
    }

    private function value(array $s, ?string $lang = null): string
    {
        $v = (string)($this->st['answers'][$s['id']] ?? '');
        switch ($s['type']) {
            case 'choice':
                $o = $this->find($this->options($s), $v);
                return $o ? $this->tr($o['full'] ?? $o['label'], $lang) : '';
            case 'date':
                $d = $this->ymd($v);
                return $d ? $this->weekday($d, $lang) . ', ' . $d->format('d.m.Y') : '';
            case 'photo':
                if ($v === '') return isset($s['skip']) ? $this->tr($s['skip']['label'], $lang) : '';
                return in_array($v, $this->st['photos'], true) ? $this->tx('photo_attached', $lang) : $v;
            default:
                if ($v === '' && isset($s['skip'])) return $this->tr($s['skip']['label'], $lang);
                return $v;
        }
    }

    private function price(): ?int
    {
        $o = $this->find($this->options($this->step('size') ?? []), $this->st['answers']['size'] ?? null);
        return isset($o['price']) ? (int)$o['price'] : null;
    }

    private function waLink(bool $withOrder): string
    {
        $lines = [$this->tx('wa_intro')];
        if ($withOrder) $lines[] = $this->tx('wa_order') . ' № ' . $this->st['order'];
        foreach ($this->lines(null, true) as $l) $lines[] = $l;
        if ($p = $this->price()) $lines[] = $this->tx('price') . ': ' . $p . ' ₼';
        return 'https://wa.me/' . $this->env['wa'] . '?text=' . rawurlencode(implode("\n", $lines));
    }

    // ===== Даты и время: те же правила, что в календаре формы заказа (site.js) =====

    private function dateOptions(): array
    {
        $out = [];
        for ($d = $this->minDate(); $d <= $this->maxDate() && count($out) < BOT_DATE_BUTTONS; $d = $d->modify('+1 day')) {
            if (!$this->closed($d)) $out[] = ['id' => $d->format('Y-m-d'), 'label' => $this->weekday($d) . ', ' . $d->format('d.m')];
        }
        return $out;
    }

    // Минимум 1 полный день на подготовку: сегодня заказ — ближайшая дата послезавтра
    private function minDate(): DateTimeImmutable
    {
        return $this->today->modify('+2 days');
    }

    private function maxDate(): DateTimeImmutable
    {
        return $this->today->modify('+60 days');
    }

    private function closed(DateTimeImmutable $d): bool
    {
        $sch = $this->env['schedule'];
        return in_array((int)$d->format('w'), $sch['weekly'], true) || in_array($d->format('Y-m-d'), $sch['closed'], true);
    }

    private function dateProblem(DateTimeImmutable $d): ?string
    {
        if ($d < $this->minDate()) {
            $first = $this->dateOptions()[0]['label'] ?? '';
            return str_replace('{date}', $first, $this->tx('date_soon'));
        }
        if ($d > $this->maxDate()) return $this->tx('date_far');
        if ($this->closed($d)) return str_replace('{date}', $this->weekday($d) . ', ' . $d->format('d.m'), $this->tx('date_closed'));
        return null;
    }

    // «31.10», «31/10/2026», «31-10-26»; без года — ближайшее такое число
    private function parseDate(string $t): ?DateTimeImmutable
    {
        if (!preg_match('/(?<!\d)(\d{1,2})\s*[.\/-]\s*(\d{1,2})(?:\s*[.\/-]\s*(\d{4}|\d{2}))?(?!\d)/', $t, $m)) return null;
        $day = (int)$m[1];
        $mon = (int)$m[2];
        $hasYear = isset($m[3]) && $m[3] !== '';
        $year = $hasYear ? (int)$m[3] : (int)$this->today->format('Y');
        if ($year < 100) $year += 2000;
        if (!checkdate($mon, $day, $year)) return null;
        $d = $this->ymd(sprintf('%04d-%02d-%02d', $year, $mon, $day));
        if (!$hasYear && $d < $this->today) $d = $d->modify('+1 year');
        return $d;
    }

    // Слоты по часу: будни 11:00–20:00, суббота — до 14:00, понедельник — не раньше 17:00
    private function slots(): array
    {
        $d  = $this->ymd((string)($this->st['answers']['date'] ?? ''));
        $wd = $d ? (int)$d->format('w') : -1;
        $first = $wd === 1 ? 17 : 11;
        $last  = $wd === 6 ? 13 : 19;
        $out = [];
        for ($h = $first; $h <= $last; $h++) {
            $v = $h . ':00–' . ($h + 1) . ':00';
            $out[] = ['id' => $v, 'label' => $v];
        }
        return $out;
    }

    private function ymd(string $v): ?DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
        return DateTimeImmutable::createFromFormat('!Y-m-d', $v, $this->tz) ?: null;
    }

    private function weekday(DateTimeImmutable $d, ?string $lang = null): string
    {
        return $this->sc['weekdays'][$lang ?? $this->lang()][(int)$d->format('w')] ?? '';
    }

    // ===== Варианты ответа =====

    private function options(array $s): array
    {
        $o = $s['options'] ?? [];
        if (isset($s['options_by'])) $o = $o[$this->st['answers'][$s['options_by']] ?? ''] ?? [];
        return array_values(array_filter($o, fn($x) => $this->matches($x['if'] ?? [])));
    }

    private function find(array $opts, $id): ?array
    {
        foreach ($opts as $o) {
            if ((string)$o['id'] === (string)$id) return $o;
        }
        return null;
    }

    // Нажатая кнопка или написанный текст → вариант. Текст сверяем с подписями на всех языках:
    // «шоколадный», «2» (второй по счёту — в WhatsApp так удобно), «3-4».
    private function pick(array $opts, array $in, bool $byNumber = true): ?array
    {
        $kind = $in['type'] ?? '';
        if ($kind === 'option') return $this->find($opts, $in['id'] ?? '');
        if ($kind !== 'text') return null;
        $t = $this->norm((string)($in['text'] ?? ''));
        if ($t === '') return null;
        if ($byNumber && ctype_digit($t) && isset($opts[(int)$t - 1])) return $opts[(int)$t - 1];
        $near = [];
        foreach ($opts as $o) {
            foreach ($this->names($o) as $n) {
                if ($n === $t) return $o;
                if (mb_strlen($t) >= 3 && mb_strlen($n) >= 3 && (str_contains($n, $t) || str_contains($t, $n))) $near[$o['id']] = $o;
            }
        }
        return count($near) === 1 ? reset($near) : null;
    }

    // Пропуск шага («Без надписи») — только кнопкой или точно таким же текстом,
    // чтобы надпись «Без надписи, только цифра 5» не потерялась
    private function isSkip(array $s, array $in): bool
    {
        if (!isset($s['skip'])) return false;
        if (($in['type'] ?? '') === 'option') return ($in['id'] ?? '') === $s['skip']['id'];
        return ($in['type'] ?? '') === 'text' && in_array($this->norm((string)$in['text']), $this->names($s['skip']), true);
    }

    private function names(array $o): array
    {
        $out = [$this->norm((string)$o['id'])];
        foreach (['label', 'full'] as $k) {
            foreach ((array)($o[$k] ?? []) as $v) $out[] = $this->norm((string)$v);
        }
        return array_values(array_unique(array_filter($out, 'strlen')));
    }

    // Вариант со ссылкой (конструктор) — сразу кнопка-ссылка: нажал и перешёл, без лишнего ответа бота
    private function labels(array $opts): array
    {
        return array_map(fn($o) => ['id' => (string)$o['id'], 'label' => $this->tr($o['label'])]
            + (isset($o['link']) ? ['url' => $this->siteUrl($o['link'])] : []), $opts);
    }

    private function linkOption(array $o): array
    {
        return [
            'id'    => $o['id'] . '-link',
            'label' => $this->tr($o['link_label'] ?? $o['label']),
            'url'   => $this->siteUrl($o['link']),
        ];
    }

    private function siteUrl(string $path): string
    {
        $lang = $this->lang();
        return rtrim($this->env['site'], '/') . ($lang === 'ru' ? '' : '/' . $lang) . $path;
    }

    // ===== Тексты =====

    private function command(string $text): ?string
    {
        $t = $this->norm($text);
        foreach ($this->sc['commands'] ?? [] as $name => $words) {
            foreach ($words as $w) {
                if ($this->norm($w) === $t) return $name;
            }
        }
        return null;
    }

    private function lang(): string
    {
        return $this->st['lang'] ?? 'ru';
    }

    // Текст из сценария: строка одна на все языки или {ru, az, en}
    private function tr($v, ?string $lang = null): string
    {
        if (!is_array($v)) return (string)$v;
        return (string)($v[$lang ?? $this->lang()] ?? $v['ru'] ?? reset($v));
    }

    private function tx(string $key, ?string $lang = null): string
    {
        return $this->tr($this->sc['texts'][$key] ?? $key, $lang);
    }

    private function step(string $id): ?array
    {
        foreach ($this->sc['steps'] as $s) {
            if ($s['id'] === $id) return $s;
        }
        return null;
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower(str_replace(['ё', 'Ё'], 'е', $s));
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s));
    }

    private function clean(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $s);
        return trim(preg_replace('/[ \t]+/u', ' ', $s));
    }

    // ===== Сообщения и история =====

    private function say(string $text, array $options = [], string $input = 'text', ?array $s = null): void
    {
        $m = ['text' => $text];
        if ($options) $m['options'] = $options;
        if ($s) {
            $m['step']  = $s['id'];
            $m['input'] = $input;
            if (isset($s['placeholder'])) $m['placeholder'] = $this->tr($s['placeholder']);
        }
        $this->out[] = $m;
        $this->log(['from' => 'bot'] + $m);
    }

    // Только в ответ, не в историю: после перезагрузки страницы ссылка снова сама не откроется
    private function openNow(string $url): void
    {
        $this->out[array_key_last($this->out)]['open'] = $url;
    }

    private function logUser(array $in): void
    {
        $m = ['from' => 'user', 'text' => mb_substr((string)($in['label'] ?? $in['text'] ?? $in['id'] ?? ''), 0, 300)];
        if (($in['type'] ?? '') === 'photo') $m['photo'] = (string)$in['url'];
        $this->log($m);
    }

    private function log(array $m): void
    {
        $this->st['history'][] = $m + ['t' => time()];
        if (count($this->st['history']) > BOT_HISTORY) {
            $this->st['history'] = array_slice($this->st['history'], -BOT_HISTORY);
        }
    }
}
