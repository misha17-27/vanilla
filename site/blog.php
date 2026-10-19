<?php
$page = 'blog';
require_once __DIR__ . '/includes/blog.php';
$posts = blog_posts();
$page_title  = $t['blog_title'];
$page_meta   = $t['blog_meta'];
$page_schema = 'CollectionPage';
if ($posts) $og_image = CANON_HOST . asset((string)reset($posts)['image']);
schema_breadcrumbs([[$t['nav_blog'], lang_path($lang, '/blog/')]]);

// Блог со списком статей — по нему поиск понимает, что это за раздел
$items = [];
foreach ($posts as $p) {
    $items[] = [
        '@type'         => 'BlogPosting',
        'headline'      => blog_text($p, 'title'),
        'url'           => CANON_HOST . blog_url($p),
        'image'         => CANON_HOST . asset((string)$p['image']),
        'datePublished' => $p['published'],
        'dateModified'  => blog_updated($p),
    ];
}
schema_add([
    '@type'      => 'Blog',
    '@id'        => canonical_url() . '#blog',
    'name'       => $t['blog_h'],
    'url'        => canonical_url(),
    'inLanguage' => $lang,
    'publisher'  => ['@id' => CANON_HOST . '/#organization'],
    'blogPost'   => $items,
]);
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="crumbs">
      <a href="<?= e(u('/')) ?>"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <span><?= e($t['nav_blog']) ?></span>
    </div>
    <h1><?= e($t['blog_h']) ?></h1>
    <p class="lead"><?= e($t['blog_lead']) ?></p>
  </div>
</section>

<section class="section blog-list">
  <div class="container">
    <?php if (!$posts): ?>
    <p class="b-empty"><?= e($t['blog_empty']) ?></p>
    <?php else: ?>
    <div class="b-grid">
      <?php $i = 0; foreach ($posts as $p) echo blog_card($p, $i++ > 2); ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
