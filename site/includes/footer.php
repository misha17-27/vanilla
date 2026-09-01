<footer class="site-footer">
  <div class="container">
    <div class="f-grid">
      <div class="f-brand">
        <img src="/assets/logo.svg" alt="Vanilla Cake" width="170" height="38">
        <p><?= e($t['footer_desc']) ?></p>
        <div class="socials">
          <a href="<?= IG_URL ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg></a>
          <a href="<?= FB_URL ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 8.5V7a1.5 1.5 0 0 1 1.5-1.5H17V2h-3a4 4 0 0 0-4 4v2.5H7.5V12H10v10h4V12h2.6l.9-3.5H14Z"/></svg></a>
          <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .3-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2.1 1c.3.2.5.3.6.4.1.2.1.7-.2 1.2Z"/></svg></a>
        </div>
      </div>
      <div>
        <h4><?= e($t['footer_menu']) ?></h4>
        <ul>
          <li><a href="/bolme/bento-tort/"><?= e($t['nav_bento']) ?></a></li>
          <?php foreach (own_categories() as $fc): ?>
          <li><a href="<?= e(cat_url($fc)) ?>"><?= e(cat_name($fc['key'])) ?></a></li>
          <?php endforeach; ?>
          <li><a href="/bolme/cake-to-go/"><?= e($t['nav_ctg']) ?></a></li>
          <li><a href="/terkibler/"><?= e($t['nav_fillings']) ?></a></li>
        </ul>
      </div>
      <div>
        <h4><?= e($t['footer_pages']) ?></h4>
        <ul>
          <li><a href="/haqqimizda/"><?= e($t['nav_about']) ?></a></li>
          <li><a href="/reyler/"><?= e($t['nav_reviews']) ?></a></li>
          <li><a href="/faq/"><?= e($t['nav_faq']) ?></a></li>
          <li><a href="/elaqe/"><?= e($t['nav_contact']) ?></a></li>
        </ul>
      </div>
      <div>
        <h4><?= e($t['footer_contact']) ?></h4>
        <ul class="f-contact">
          <li><a href="tel:<?= PHONE_TEL ?>"><?= PHONE_DISPLAY ?></a></li>
          <li><a href="mailto:<?= EMAIL ?>"><?= EMAIL ?></a></li>
          <li><a href="<?= IG_URL ?>" target="_blank" rel="noopener"><?= IG_HANDLE ?></a></li>
          <li><span><?= e($t['hours']) ?></span></li>
        </ul>
      </div>
    </div>
    <div class="f-bottom">
      <span>© <?= date('Y') ?> Vanilla.az — <?= e($t['footer_rights']) ?></span>
      <span class="f-slogan">Made with love</span>
    </div>
  </div>
</footer>

<a class="ig-fab" href="<?= IG_URL ?>" target="_blank" rel="noopener">
  <span class="ig-fab-ico">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.6" cy="6.4" r="1.3" fill="currentColor" stroke="none"/></svg>
  </span>
  <span class="ig-fab-txt">
    <b><?= e($t['ig_fab_t']) ?></b>
    <small><?= e($t['ig_fab_d']) ?></small>
  </span>
</a>

<div class="mob-cta">
  <a class="btn btn-wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e($t['btn_wa']) ?></a>
</div>

<script src="/assets/site.js?v=<?= @filemtime(__DIR__ . '/../assets/site.js') ?>" defer></script>

<?php
// ===== Разметка schema.org: организация, сайт, страница + узлы конкретной страницы =====
$orgId  = CANON_HOST . '/#organization';
$siteId = CANON_HOST . '/#website';
$graph  = [
    [
        '@type'       => ['Bakery', 'Organization'],
        '@id'         => $orgId,
        'name'        => 'Vanilla Cake',
        'alternateName' => 'Vanilla.az',
        'url'         => CANON_HOST . '/',
        'logo'        => ['@type' => 'ImageObject', 'url' => CANON_HOST . '/assets/logo.svg'],
        'image'       => CANON_HOST . '/assets/og-cover.jpg',
        'description' => $t['footer_desc'],
        'telephone'   => PHONE_TEL,
        'email'       => EMAIL,
        'priceRange'  => '25–100 AZN',
        'currenciesAccepted' => 'AZN',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Bakı',
            'addressCountry'  => 'AZ',
        ],
        'geo'         => ['@type' => 'GeoCoordinates', 'latitude' => MAP_LAT, 'longitude' => MAP_LNG],
        'hasMap'      => MAP_URL,
        'openingHoursSpecification' => [
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '11:00', 'closes' => '20:00'],
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Saturday', 'opens' => '11:00', 'closes' => '14:00'],
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Sunday', 'opens' => '00:00', 'closes' => '00:00'],
        ],
        'areaServed'  => ['@type' => 'City', 'name' => 'Bakı'],
        'sameAs'      => array_values(array_filter([IG_URL, FB_URL])),
    ],
    [
        '@type'      => 'WebSite',
        '@id'        => $siteId,
        'url'        => CANON_HOST . '/',
        'name'       => 'Vanilla Cake',
        'publisher'  => ['@id' => $orgId],
        'inLanguage' => $lang,
    ],
    [
        '@type'       => $page_schema ?? 'WebPage',
        '@id'         => canonical_url() . '#webpage',
        'url'         => canonical_url(),
        'name'        => $page_title,
        'description' => $page_meta,
        'isPartOf'    => ['@id' => $siteId],
        'about'       => ['@id' => $orgId],
        'inLanguage'  => $lang,
        'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $og_image],
    ] + ($page_schema_extra ?? []),
];
foreach ($SCHEMA as $node) $graph[] = $node;
?>
<script type="application/ld+json"><?= json_encode(
    ['@context' => 'https://schema.org', '@graph' => $graph],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?></script>
</body>
</html>
