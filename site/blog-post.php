<?php
// Статья блога. Открывается только через router.php (/blog/{slug}/)
if (!isset($ROUTE_SLUG)) {
    header('Location: /blog/', true, 301);
    exit;
}
$page = 'blog';
require_once __DIR__ . '/includes/blog.php';
$post = blog_post($ROUTE_SLUG);
if (!$post) {
    header('Location: ' . u('/blog/'), true, 302);
    exit;
}
$title   = blog_text($post, 'title');
$updated = blog_updated($post);
$img     = asset((string)$post['image']);
$faq     = [];
$bodyHtml = blog_render($post[$lang]['body'], $faq);
$toc     = blog_toc($post[$lang]['body']);

$page_title = blog_text($post, 'seo_title') ?: $title . ' — Vanilla Cake';
$page_meta  = blog_text($post, 'seo_desc') ?: blog_text($post, 'excerpt');
$og_image   = CANON_HOST . $img;
$og_type    = 'article';
$og_article = ['published' => $post['published'], 'modified' => $updated];
schema_breadcrumbs([
    [$t['nav_blog'], lang_path($lang, '/blog/')],
    [$title, lang_path($lang, blog_path($post))],
]);
schema_add([
    '@type'            => 'BlogPosting',
    '@id'              => canonical_url() . '#article',
    'headline'         => $title,
    'description'      => $page_meta,
    'image'            => [CANON_HOST . $img],
    'datePublished'    => $post['published'],
    'dateModified'     => $updated,
    'inLanguage'       => $lang,
    'wordCount'        => blog_words($post),
    'author'           => ['@type' => 'Organization', 'name' => 'Vanilla Cake', 'url' => CANON_HOST . '/'],
    'publisher'        => ['@id' => CANON_HOST . '/#organization'],
    'mainEntityOfPage' => ['@id' => canonical_url() . '#webpage'],
]);
if ($faq) {
    schema_add([
        '@type'      => 'FAQPage',
        '@id'        => canonical_url() . '#faq',
        'mainEntity' => array_map(fn($qa) => [
            '@type'          => 'Question',
            'name'           => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => html_entity_decode(strip_tags(blog_inline($qa[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8')],
        ], $faq),
    ]);
}
// другие статьи внизу — свежие, кроме этой
$more = array_slice(array_values(array_filter(blog_posts(), fn($p) => $p['slug'] !== $post['slug'])), 0, 3);
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero b-hero">
  <div class="container">
    <div class="crumbs">
      <a href="<?= e(u('/')) ?>"><?= e($t['breadcrumb_home']) ?></a>
      <span class="sep">/</span>
      <a href="<?= e(u('/blog/')) ?>"><?= e($t['nav_blog']) ?></a>
      <span class="sep">/</span>
      <span><?= e($title) ?></span>
    </div>
    <h1><?= e($title) ?></h1>
    <p class="b-meta">
      <time datetime="<?= e($post['published']) ?>"><?= e(blog_date($post['published'])) ?></time>
      · <?= e(sprintf($t['blog_min'], blog_minutes($post))) ?>
      <?php if ($updated !== $post['published']): ?>
      · <?= e($t['blog_updated']) ?> <time datetime="<?= e($updated) ?>"><?= e(blog_date($updated)) ?></time>
      <?php endif; ?>
    </p>
  </div>
</section>

<section class="section b-section">
  <div class="container">
    <article class="b-article">
      <figure class="b-cover">
        <img src="<?= e($img) ?>" alt="<?= e(blog_text($post, 'image_alt') ?: $title) ?>" width="800" height="800" fetchpriority="high">
      </figure>
      <?php if (count($toc) >= 3): ?>
      <nav class="b-toc" aria-label="<?= e($t['blog_toc']) ?>">
        <b><?= e($t['blog_toc']) ?></b>
        <ol>
          <?php foreach ($toc as [$id, $label]): ?>
          <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>
      <?php endif; ?>
      <div class="b-body">
        <?= $bodyHtml ?>
      </div>
    </article>

    <?php if ($more): ?>
    <div class="b-more-wrap">
      <h2 class="b-more-h"><?= e($t['blog_more']) ?></h2>
      <div class="b-grid">
        <?php foreach ($more as $p) echo blog_card($p); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
