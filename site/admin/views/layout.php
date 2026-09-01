<?php
$bare = !admin_logged();
$nav = [
    'Магазин' => [
        ['/admin/',          'dashboard', 'Обзор'],
        ['/admin/orders',    'orders',    'Заказы'],
        ['/admin/customers', 'customers', 'Клиенты'],
    ],
    'Каталог' => [
        ['/admin/products',   'products',   'Товары'],
        ['/admin/categories', 'categories', 'Категории'],
        ['/admin/designs',    'designs',    'Дизайны клиентов'],
    ],
    'Настройки' => [
        ['/admin/seo',      'seo',      'SEO страниц'],
        ['/admin/settings', 'settings', 'Контакты и карта'],
        ['/admin/account',  'account',  'Пароль'],
    ],
];
$icons = [
    'orders'    => '<path d="M6 2h12l2 5H4z"/><path d="M5 7v13h14V7"/><path d="M9 11a3 3 0 0 0 6 0"/>',
    'customers' => '<circle cx="9" cy="8" r="3.4"/><path d="M3 20c0-3.3 2.7-5.4 6-5.4s6 2.1 6 5.4"/><path d="M16 5.2a3.4 3.4 0 0 1 0 6.6"/><path d="M17.5 14.9c2.1.6 3.5 2.3 3.5 5.1"/>',
    'categories'=> '<rect x="3" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.6"/>',
    'seo'       => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
    'dashboard' => '<path d="M3 12l9-8 9 8"/><path d="M5 10v10h14V10"/>',
    'products'  => '<path d="M3 7l9-4 9 4-9 4z"/><path d="M3 7v10l9 4 9-4V7"/>',
    'designs'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M21 16l-5-5-6 6"/>',
    'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1A1.7 1.7 0 0 0 9 19.4a1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    'account'   => '<path d="M12 3l7.5 3v5.2c0 4.6-3.1 8.3-7.5 9.8-4.4-1.5-7.5-5.2-7.5-9.8V6z"/><path d="M9 12l2 2 4-4"/>',
];
$here = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$isOn = function (string $href) use ($here): bool {
    return $href === '/admin/' ? in_array($here, ['/admin/', '/admin'], true) : str_starts_with($here, $href);
};
?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — Vanilla Cake</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍰</text></svg>">
<link rel="stylesheet" href="/admin/assets/admin.css?v=3">
</head>
<body class="<?= $bare ? 'bare' : '' ?>">

<?php if ($bare): ?>
  <main class="auth">
    <?php require __DIR__ . '/login.php'; ?>
  </main>
<?php else: ?>
  <div class="shell">
    <aside class="side">
      <a class="brand" href="/admin/">
        <img src="/assets/logo.svg" alt="Vanilla Cake" width="130" height="30">
        <span>Admin</span>
      </a>
      <nav>
        <?php foreach ($nav as $group => $links): ?>
          <div class="navgroup"><?= e($group) ?></div>
          <?php foreach ($links as [$href, $icon, $label]): ?>
          <a href="<?= e($href) ?>" class="<?= $isOn($href) ? 'on' : '' ?>">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$icon] ?></svg>
            <?= e($label) ?>
            <?php if ($icon === 'products'): ?><i class="tally"><?= count($products) ?></i><?php endif; ?>
            <?php if ($icon === 'orders' && $newOrders): ?><i class="tally hot"><?= $newOrders ?></i><?php endif; ?>
            <?php if ($icon === 'customers' && $customers): ?><i class="tally"><?= count($customers) ?></i><?php endif; ?>
            <?php if ($icon === 'categories'): ?><i class="tally"><?= count($cats) ?></i><?php endif; ?>
            <?php if ($icon === 'designs' && $designs): ?><i class="tally"><?= count($designs) ?></i><?php endif; ?>
          </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>
      <div class="side-foot">
        <a href="/" target="_blank" rel="noopener">Открыть сайт ↗</a>
        <form method="post"><input type="hidden" name="action" value="logout"><button class="linkish">Выйти</button></form>
      </div>
    </aside>

    <main class="main">
      <header class="top">
        <h1><?= e($title) ?></h1>
        <div class="top-acts">
          <a class="top-btn" href="/" target="_blank" rel="noopener">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
            Сайт
          </a>
          <?php if ($view === 'products'): ?>
          <a class="top-btn accent" href="/admin/products?add=1">+ Добавить торт</a>
          <?php elseif ($view === 'categories'): ?>
          <a class="top-btn accent" href="/admin/categories?add=1">+ Добавить категорию</a>
          <?php endif; ?>
          <form method="post" class="inline">
            <input type="hidden" name="action" value="logout">
            <button class="top-btn out">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4"/><path d="M16 16l4-4-4-4"/><path d="M20 12H9"/></svg>
              Выйти
            </button>
          </form>
        </div>
      </header>

      <?php if ($note): ?><div class="flash <?= e($note['kind']) ?>"><?= e($note['message']) ?></div><?php endif; ?>

      <?php require __DIR__ . '/' . $view . '.php'; ?>
    </main>
  </div>
<?php endif; ?>

<script>
document.addEventListener('click', function (e) {
  var el = e.target.closest('[data-confirm]');
  if (el && !confirm(el.dataset.confirm)) e.preventDefault();
});
</script>
</body>
</html>
